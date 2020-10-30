<?php

namespace Mush\Daedalus\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Class UsersController
 * @package Mush\Controller
 * @Route(path="/daedalus")
 */
class DaedalusController extends AbstractFOSRestController
{
    private DaedalusServiceInterface $daedalusService;
    private GameConfigServiceInterface $gameConfigService;

    /**
     * DaedalusController constructor.
     * @param DaedalusServiceInterface $daedalusService
     * @param GameConfigServiceInterface $gameConfigService
     */
    public function __construct(
        DaedalusServiceInterface $daedalusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->daedalusService = $daedalusService;
        $this->gameConfigService = $gameConfigService;
    }

    /**
     * Display Daedalus informations
     *
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The daedalus id",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Daedalus")
     * @Security(name="Bearer")
     * @Rest\Get(path="/{id}")
     */
    public function getDaedalusAction(Request $request): Response
    {
        $daedalus = $this->daedalusService->findById($request->get('id'));

        $view = $this->view($daedalus, 200);

        return $this->handleView($view);
    }

    /**
     * Create a Daedalus
     *
     * @OA\Tag(name="Daedalus")
     * @Security(name="Bearer")
     * @Rest\Post(path="")
     */
    public function createDaedalusAction(): Response
    {
        $daedalus = $this->daedalusService->createDaedalus($this->gameConfigService->getConfig());

        $view = $this->view($daedalus, 201);

        return $this->handleView($view);
    }
}
