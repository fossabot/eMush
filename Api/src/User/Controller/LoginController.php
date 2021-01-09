<?php

namespace Mush\User\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Mush\User\Service\LoginService;
use Mush\User\Service\UserServiceInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class LoginController.
 *
 * @Route(path="")
 */
class LoginController extends AbstractFOSRestController
{
    private JWTTokenManagerInterface $jwtManager;
    private UserServiceInterface $userService;
    private LoginService $loginService;
    private string $alphaPassphrase;

    public function __construct(
        string $alphaPassphrase,
        JWTTokenManagerInterface $jwtManager,
        UserServiceInterface $userService,
        LoginService $loginService
    ) {
        $this->jwtManager = $jwtManager;
        $this->userService = $userService;
        $this->loginService = $loginService;
        $this->alphaPassphrase = $alphaPassphrase;
    }

    /**
     * Login.
     *
     * @OA\RequestBody (
     *      description="Input data format",
     *
     * @OA\MediaType (
     *             mediaType="application/json",
     *
     * @OA\Schema (
     *              type="object",
     *
     * @OA\Property (
     *                     property="username",
     *                     description="The user username",
     *                     type="string",
     *                 ),
     *             )
     *         )
     *     )
     *
     * @OA\Tag (name="Login")
     *
     * @Post (name="username_login", path="/token")
     */
    public function tokenAction(Request $request): View
    {
        $code = $request->get('code');

        if (empty($code)) {
            throw new UnauthorizedHttpException('Bad credentials');
        }

        $user = $this->loginService->login($code);

        $token = $this->jwtManager->create($user);

        return $this->view(['token' => $token]);
    }

    /**
     * @Get(name="callback", path="/callback")
     */
    public function callbackAction(Request $request): RedirectResponse
    {
        $code = $request->get('code');
        $state = $request->get('state');

        $token = $this->loginService->verifyCode($code);
        $parameters = http_build_query(['code' => $token]);

        return $this->redirect($state . '?' . $parameters);
    }

    /**
     * @GET(name="redirect_login", path="/authorize")
     */
    public function redirectAction(Request $request): Response
    {
        $redirectUri = $request->get('redirect_uri');
        $passphrase = $request->get('passphrase');

        if (!$redirectUri || !$passphrase) {
            throw new UnauthorizedHttpException('Bad credentials: missing redirect uri');
        }

        if ($passphrase !== $this->alphaPassphrase) {
            return $this->redirect($redirectUri . '?error=invalid passphrase');
        }

        $uri = $this->loginService->getAuthorizationUri('base', $redirectUri);

        return $this->redirect($uri);
    }
}
