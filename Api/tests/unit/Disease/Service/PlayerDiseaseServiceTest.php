<?php

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\DiseaseCause;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;

class PlayerDiseaseServiceTest extends TestCase
{
    private PlayerDiseaseService $playerDiseaseService;

    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var DiseaseConfigRepository | Mockery\Mock */
    private DiseaseConfigRepository $diseaseConfigRepository;

    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->diseaseConfigRepository = Mockery::mock(DiseaseConfigRepository::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);

        $this->playerDiseaseService = new PlayerDiseaseService(
            $this->entityManager,
            $this->diseaseConfigRepository,
            $this->randomService,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testHandleDiseaseForCause()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseCause = new DiseaseCause();
        $diseaseCause->setName('cause');
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setCauses(new ArrayCollection([$diseaseCause]));

        $this->diseaseConfigRepository
            ->shouldReceive('findByCauses')
            ->andReturn([$diseaseConfig])
            ->twice()
        ;

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(false)
            ->once()
        ;

        $this->playerDiseaseService->handleDiseaseForCause('cause', $player);

        $this->randomService
            ->shouldReceive('isSuccessful')
            ->andReturn(true)
            ->once()
        ;

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null
        ])->once();

        $this->playerDiseaseService->handleDiseaseForCause('cause', $player);
    }
}
