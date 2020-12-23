<?php

namespace Mush\User\Service;

use Etwin\Auth\AccessTokenAuthContext;
use Etwin\Client\Auth;
use Etwin\Client\HttpEtwinClient;
use Etwin\OauthClient\RfcOauthClient;
use GuzzleHttp\Exception\GuzzleException;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Mush\User\Entity\User;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LoginService
{
    private UserServiceInterface $userService;
    private RfcOauthClient $oauthClient;
    private JWTEncoderInterface $jwtEncoder;
    private string $etwinUri;

    public function __construct(
        string $etwinUri,
        string $authorizeUri,
        string $tokenUri,
        string $oauthCallback,
        string $clientId,
        string $clientSecret,
        UserServiceInterface $userService,
        JWTEncoderInterface $jwtEncoder
    ) {
        $this->userService = $userService;
        $this->userService = $userService;
        $this->etwinUri = $etwinUri;
        $this->jwtEncoder = $jwtEncoder;
        $this->oauthClient = new RfcOauthClient(
            $authorizeUri,
            $tokenUri,
            $oauthCallback,
            $clientId,
            $clientSecret,
        );
    }

    public function login(string $codeToken): User
    {
        try {
            $decodedToken = $this->jwtEncoder->decode($codeToken);
        } catch (JWTDecodeFailureException $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        }

        if (!$decodedToken || !($code = $decodedToken['code'])) {
            throw new UnauthorizedHttpException('Bad Credentials');
        }

        $user = $this->userService->findUserByNonceCode($code);

        if ($user === null) {
            throw new UnauthorizedHttpException('Bad Credentials');
        }

        $user
            ->setNonceCode(null)
            ->setNonceExpiryDate(null)
        ;
        $this->userService->persist($user);

        return $user;
    }

    public function verifyCode(string $code): string
    {
        try {
            $accessToken = $this->oauthClient->getAccessTokenSync($code);
        } catch (GuzzleException $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        } catch (\JsonException $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        }

        $apiClient = new HttpEtwinClient($this->etwinUri);
        $apiUser = $apiClient->getSelf(Auth::fromToken($accessToken->getAccessToken()));

        if (!$apiUser instanceof AccessTokenAuthContext) {
            throw new \LogicException('Auth context not supported');
        }

        $userId = $apiUser->getUser()->getId()->getInner()->toString();
        $user = $this->userService->findUserByUserId($userId);

        if ($user === null) {
            $username = $apiUser->getUser()->getDisplayName()->getCurrent()->getInner()->toString();
            $user = $this->userService->createUser($userId, $username);
        }

        $nonce = bin2hex(openssl_random_pseudo_bytes(10)); // 20 chars

        $expiryTime = time() + 60; // 1 minute expiration

        $user
            ->setNonceCode($nonce)
            ->setNonceExpiryDate((new \DateTime())->setTimestamp($expiryTime))
        ;

        $this->userService->persist($user);

        $token = $this->jwtEncoder
            ->encode([
                    'code' => $nonce,
                    'exp' => $expiryTime,
                ]
            );

        return $token;
    }

    public function getAuthorizationUri(string $scope, ?string $state): string
    {
        return (string) $this->oauthClient->getAuthorizationUri($scope, $state ?? '');
    }
}
