<?php

namespace Mush\Room\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;
use Mush\Room\Repository\RoomRepository;

class RoomService implements RoomServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RoomRepository $repository;
    private GameEquipmentServiceInterface $equipmentService;

    /**
     * RoomService constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RoomRepository $repository,
        GameEquipmentServiceInterface $equipmentService
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->equipmentService = $equipmentService;
    }

    public function persist(Room $room): Room
    {
        $this->entityManager->persist($room);
        $this->entityManager->flush();

        return $room;
    }

    public function findById(int $id): ?Room
    {
        return $this->repository->find($id);
    }

    public function createRoom(RoomConfig $roomConfig, Daedalus $daedalus): Room
    {
        $room = new Room();
        $room->setName($roomConfig->getName());

        $this->persist($room);

        // @FIXME how to simplify that?
        foreach ($roomConfig->getDoors() as $doorName) {
            if (
                $roomDoor = $daedalus->getRooms()->filter( //If door already exist
                    function (Room $room) use ($doorName) {
                        return $room->getDoors()->exists(function ($key, Door $door) use ($doorName) {
                            return $door->getName() === $doorName;
                        });
                    }
                )->first()
            ) {
                $door = $roomDoor->getDoors()->filter(function (Door $door) use ($doorName) {
                    return $door->getName() === $doorName;
                })->first();
            } else { //else create new door
                $door = new Door();
                $door->setName($doorName);
            }

            $room->addDoor($door);
        }

        foreach ($roomConfig->getItems() as $itemName) {
            $item = $daedalus
                ->getGameConfig()
                ->getEquipmentsConfig()
                ->filter(fn (EquipmentConfig $item) => $item->getName() === $itemName)->first()
            ;
            $gameItem = $this->equipmentService->createGameEquipment($item, $daedalus);
            $room->addEquipment($gameItem);
        }

        foreach ($roomConfig->getEquipments() as $equipmentName) {
            $equipment = $daedalus
                ->getGameConfig()
                ->getEquipmentsConfig()
                ->filter(fn (EquipmentConfig $equipment) => $equipment->getName() === $equipmentName)->first()
            ;
            $gameEquipment = $this->equipmentService->createGameEquipment($equipment, $daedalus);
            $room->addEquipment($gameEquipment);
        }

        return $this->persist($room);
    }
}
