<?php

namespace Mush\Daedalus\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Dto\DaedalusCreateRequest;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Daedalus\Service\DaedalusWidgetServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Repository\PlayerInfoRepository;
use Mush\User\Entity\User;
use Mush\User\Enum\RoleEnum;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UsersController.
 *
 * @Route(path="/daedaluses")
 */
class DaedalusController extends AbstractFOSRestController
{
    private DaedalusServiceInterface $daedalusService;
    private DaedalusWidgetServiceInterface $daedalusWidgetService;
    private TranslationServiceInterface $translationService;
    private PlayerInfoRepository $playerInfoRepository;
    private ValidatorInterface $validator;
    private RandomServiceInterface $randomService;

    public function __construct(
        DaedalusServiceInterface $daedalusService,
        DaedalusWidgetServiceInterface $daedalusWidgetService,
        TranslationServiceInterface $translationService,
        PlayerInfoRepository $playerInfoRepository,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService
    ) {
        $this->daedalusService = $daedalusService;
        $this->daedalusWidgetService = $daedalusWidgetService;
        $this->translationService = $translationService;
        $this->playerInfoRepository = $playerInfoRepository;
        $this->validator = $validator;
        $this->randomService = $randomService;
    }

    /**
     * Display available daedalus and characters.
     *
     * @OA\Tag (name="Daedalus")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Get (path="/available-characters")
     */
    public function getAvailableCharacter(Request $request): View
    {
        $name = $request->get('name', '');

        $daedalus = $this->daedalusService->findAvailableDaedalus($name);

        if ($daedalus === null) {
            return $this->view(['error' => 'Daedalus not found'], 404);
        }

        $availableCharacters = $this->daedalusService->findAvailableCharacterForDaedalus($daedalus);
        $availableCharacters = $this->randomService->getRandomElements($availableCharacters->toArray(), 4);
        $characters = [];
        /** @var CharacterConfig $character */
        foreach ($availableCharacters as $character) {
            $characters[] = [
                'key' => $character->getCharacterName(),
                'name' => $this->translationService->translate(
                    $character->getCharacterName() . '.name',
                    [],
                    'characters',
                    $daedalus->getLanguage()
                ),
                'abstract' => $this->translationService->translate(
                    $character->getCharacterName() . '.abstract',
                    [],
                    'characters',
                    $daedalus->getLanguage()
                ),
            ];
        }

        return $this->view(['daedalus' => $daedalus->getId(), 'characters' => $characters], 200);
    }

    /**
     * Display daedalus minimap.
     *
     * @OA\Tag (name="Daedalus")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Get(path="/{id}/minimap", requirements={"id"="\d+"})
     */
    public function getDaedalusMinimapsAction(Daedalus $daedalus): View
    {
        /** @var User $user */
        $user = $this->getUser();
        $playerInfo = $this->playerInfoRepository->findCurrentGameByUser($user);

        if (!$playerInfo) {
            throw new AccessDeniedException('User should be in game');
        }

        return $this->view($this->daedalusWidgetService->getMinimap($daedalus, $playerInfo->getPlayer()), 200);
    }

    /**
     * Create a Daedalus.
     *
     * @OA\RequestBody (
     *      description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *      @OA\Schema(
     *              type="object",
     *                  @OA\Property(
     *                     property="name",
     *                     description="The name of the Daedalus",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="config",
     *                     description="The daedalus config",
     *                     type="integer",
     *                 ),
     *                 @OA\Property(
     *                     property="language",
     *                     description="The language for this daedalus",
     *                     type="string"
     *                 )
     *             )
     *             )
     *         )
     *     )
     * @OA\Tag (name="Daedalus")
     *
     * @Security (name="Bearer")
     *
     * @ParamConverter("daedalusCreateRequest", converter="DaedalusCreateRequestConverter")
     * @Rest\Post(path="/create-daedalus", requirements={"id"="\d+"})
     */
    public function createDaedalus(DaedalusCreateRequest $daedalusCreateRequest): View
    {
        if (count($violations = $this->validator->validate($daedalusCreateRequest))) {
            return $this->view($violations, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User $user */
        $user = $this->getUser();

        $userRoles = $user->getRoles();
        if (!(in_array(RoleEnum::SUPER_ADMIN, $userRoles, true) ||
            in_array(RoleEnum::ADMIN, $userRoles, true))) {
            throw new AccessDeniedException('User is not an admin');
        }

        /** @var GameConfig $gameConfig */
        $gameConfig = $daedalusCreateRequest->getConfig();

        $this->daedalusService->createDaedalus(
            $gameConfig,
            $daedalusCreateRequest->getName(),
            $daedalusCreateRequest->getLanguage()
        );

        return $this->view(null, 200);
    }

    /**
     * Destroy the specified Daedalus.
     *
     * @OA\Tag (name="Daedalus")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Post(path="/destroy-daedalus/{id}", requirements={"id"="\d+"})
     */
    public function destroyDaedalus(Request $request): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $userRoles = $user->getRoles();
        if (!(in_array(RoleEnum::SUPER_ADMIN, $userRoles, true) ||
            in_array(RoleEnum::ADMIN, $userRoles, true))) {
            throw new AccessDeniedException('User is not an admin');
        }

        $daedalusId = $request->get('id');

        /** @var Daedalus $daedalus */
        $daedalus = $this->daedalusService->findById($daedalusId);
        if ($daedalus === null) {
            return $this->view(['error' => 'Daedalus not found'], 404);
        }
        if ($daedalus->getDaedalusInfo()->isDaedalusFinished()) {
            return $this->view(['error' => 'Daedalus is already finished'], 400);
        }

        $this->daedalusService->endDaedalus(
            $daedalus,
            EndCauseEnum::SUPER_NOVA,
            new \DateTime()
        );

        return $this->view(null, 200);
    }

    /**
     * Destroy all non finished Daedaluses.
     *
     * @OA\Tag (name="Daedalus")
     *
     * @Security (name="Bearer")
     *
     * @Rest\Post(path="/destroy-all-daedaluses")
     */
    public function destroyAllDaedaluses(): View
    {
        /** @var User $user */
        $user = $this->getUser();

        $userRoles = $user->getRoles();
        if (!(in_array(RoleEnum::SUPER_ADMIN, $userRoles, true) ||
            in_array(RoleEnum::ADMIN, $userRoles, true))) {
            throw new AccessDeniedException('User is not an admin');
        }

        $daedaluses = $this->daedalusService->findAllNonFinishedDaedaluses();
        if (count($daedaluses) === 0) {
            return $this->view(['error' => 'No daedaluses found'], 404);
        }

        foreach ($daedaluses as $daedalus) {
            $this->daedalusService->endDaedalus(
                $daedalus,
                EndCauseEnum::SUPER_NOVA,
                new \DateTime()
            );
        }

        return $this->view(null, 200);
    }
}
