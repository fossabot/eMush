<?php

namespace Mush\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameStatusEnum;

class CycleService implements CycleServiceInterface
{
    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
    }

    public function handleCycleChange(\DateTime $dateTime, Daedalus $daedalus): int
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();
        $daedalusConfig = $daedalusInfo->getGameConfig()->getDaedalusConfig();

        if (!in_array($daedalusInfo->getGameStatus(), [GameStatusEnum::STARTING, GameStatusEnum::CURRENT])) {
            return 0;
        }

        $dateDaedalusLastCycle = $daedalus->getCycleStartedAt();
        if ($dateDaedalusLastCycle === null) {
            throw new \LogicException('Daedalus should have a CycleStartedAt Value');
        } else {
            $dateDaedalusLastCycle = clone $dateDaedalusLastCycle;
        }

        $cycleElapsed = $this->getNumberOfCycleElapsed($dateDaedalusLastCycle, $dateTime, $daedalus);

        if ($cycleElapsed > 0) {
            if ($this->cycleChangeLastedForTooLong($daedalus)) {
                $this->skipCycleChange($daedalus);

                return $cycleElapsed;
            }

            $daedalus->setIsCycleChange(true);
            $this->entityManager->persist($daedalus);
            $this->entityManager->flush();

            try {
                for ($i = 0; $i < $cycleElapsed; ++$i) {
                    $dateDaedalusLastCycle->add(new \DateInterval('PT' . strval($daedalusConfig->getCycleLength()) . 'M'));
                    $cycleEvent = new DaedalusCycleEvent(
                        $daedalus,
                        [EventEnum::NEW_CYCLE],
                        $dateDaedalusLastCycle
                    );
                    $this->eventService->callEvent($cycleEvent, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);

                    // Do not continue make cycle if Daedalus is finish
                    if ($daedalusInfo->getGameStatus() === GameStatusEnum::FINISHED) {
                        break;
                    }
                }
            } catch (\Exception $exception) {
            } finally {
                $daedalus->setCycleStartedAt($dateDaedalusLastCycle);
                $daedalus->setIsCycleChange(false);
                $this->entityManager->persist($daedalus);
                $this->entityManager->flush();
            }
        }

        return $cycleElapsed;
    }

    public function getDateStartNextCycle(Daedalus $daedalus): \DateTime
    {
        $daedalusConfig = $daedalus->getGameConfig()->getDaedalusConfig();

        if (($dateDaedalusLastCycle = $daedalus->getCycleStartedAt()) === null) {
            throw new \LogicException('Daedalus should have a CycleStartedAt Value');
        }

        $nextCycleStartAt = clone $dateDaedalusLastCycle;

        return $nextCycleStartAt->add(new \DateInterval('PT' . strval($daedalusConfig->getCycleLength()) . 'M'));
    }

    // get day cycle from date (value between 1 and $gameConfig->getCyclePerGameDay())
    public function getInDayCycleFromDate(\DateTime $date, ClosedDaedalus|Daedalus $daedalus): int
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();

        $gameConfig = $daedalusInfo->getGameConfig();
        $localizationConfig = $daedalusInfo->getLocalizationConfig();
        $daedalusConfig = $gameConfig->getDaedalusConfig();

        $timeZoneDate = $date->setTimezone(new \DateTimeZone($localizationConfig->getTimeZone()));
        $minutes = intval($timeZoneDate->format('i'));
        $hours = intval($timeZoneDate->format('H'));

        return (int) (floor(
            ($minutes + $hours * 60) / $daedalusConfig->getCycleLength() + 1
        ) - 1) % $daedalusConfig->getCyclePerGameDay() + 1;
    }

    /**
     * Get Daedalus first cycle date
     * First actual cycle of the ship (ie: for 3h cycle in fr, if the ship start C8, then it will be 21h:00).
     */
    public function getDaedalusStartingCycleDate(Daedalus $daedalus): \DateTime
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();

        $timeConfig = $daedalusInfo->getLocalizationConfig();
        $daedalusConfig = $daedalusInfo->getGameConfig()->getDaedalusConfig();

        $firstCycleDate = $daedalus->getCreatedAt() ?? new \DateTime();

        $firstDayDate = clone $firstCycleDate;
        $firstDayDate
            ->setTimezone(new \DateTimeZone($timeConfig->getTimeZone()))
            ->setTime(0, 0, 0, 0)
            ->setTimezone(new \DateTimeZone('UTC'))
        ;

        $gameDayLength = intval($daedalusConfig->getCyclePerGameDay() * $daedalusConfig->getCycleLength()); // in min
        $numberOfCompleteDay = intval($this->getDateIntervalAsMinutes($firstCycleDate, $firstDayDate) / $gameDayLength);
        $minutesBetweenDayStartAndDaedalusFirstCycle = $numberOfCompleteDay * $gameDayLength + (($daedalus->getCycle() - 1) * $daedalusConfig->getCycleLength());

        return $firstDayDate->add(new \DateInterval('PT' . strval($minutesBetweenDayStartAndDaedalusFirstCycle) . 'M'));
    }

    private function getNumberOfCycleElapsed(\DateTime $start, \DateTime $end, Daedalus $daedalus): int
    {
        $daedalusInfo = $daedalus->getDaedalusInfo();
        $localizationConfig = $daedalusInfo->getLocalizationConfig();
        $daedalusConfig = $daedalusInfo->getGameConfig()->getDaedalusConfig();
        $start = clone $start;
        $end = clone $end;
        $end->setTimezone(new \DateTimeZone($localizationConfig->getTimeZone()));
        $start->setTimezone(new \DateTimeZone($localizationConfig->getTimeZone()));

        $differencesInMinutes = $this->getDateIntervalAsMinutes($start, $end);

        return intval(floor($differencesInMinutes / $daedalusConfig->getCycleLength()));
    }

    private function getDateIntervalAsMinutes(\DateTime $dateStart, \DateTime $dateEnd): int
    {
        $dateInterval = $dateEnd->diff($dateStart);

        return intval($dateInterval->format('%a')) * 24 * 60 +
                intval($dateInterval->format('%H')) * 60 +
                intval($dateInterval->format('%i'));
    }

    // TODO : temporary function
    // Temporary function to skip cycle change if it's too long (more than 1 cycle)
    public function handleStuckedDaedalus(Daedalus $daedalus): bool
    {
        if ($this->cycleChangeLastedForTooLong($daedalus)) {
            $this->skipCycleChange($daedalus);

            return true;
        }

        return false;
    }

    // TODO : temporary function
    private function cycleChangeLastedForTooLong(Daedalus $daedalus): bool
    {
        if (!$daedalus->isCycleChange()) {
            return false;
        }

        $lastDaedalusUpdateDate = $daedalus->getUpdatedAt();
        try {
            $cycleStartedAtDate = $daedalus->getCycleStartedAt();
        } catch (\Exception $e) {
            $cycleStartedAtDate = new \DateTime();
        } finally {
            return $this->getDateIntervalAsMinutes($lastDaedalusUpdateDate, $cycleStartedAtDate) > $daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength(); // 1 cycle tolerance
        }
    }

    // TODO : temporary function
    private function skipCycleChange(Daedalus $daedalus): void
    {
        $daedalus->setCycleStartedAt(new \DateTime());
        $daedalus->setIsCycleChange(false);
        $this->entityManager->persist($daedalus);
        $this->entityManager->flush();
    }
}
