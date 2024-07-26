<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class JWTAuthenticator extends AbstractAuthenticator
{
    private $jwtManager;
    private $userProvider;

    public function __construct(JWTTokenManagerInterface $jwtManager, UserProviderInterface $userProvider)
    {
        $this->jwtManager = $jwtManager;
        $this->userProvider = $userProvider;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        try {
            $payload = $this->jwtManager->parse($token);
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        $username = $payload['username'] ?? null;

        if (null === $username) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        return new SelfValidatingPassport(new UserBadge($username, function ($userIdentifier) {
            return $this->userProvider->loadUserByIdentifier($userIdentifier);
        }));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => $exception->getMessageKey()], Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Let the request continue
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(['error' => 'Authentication Required'], Response::HTTP_UNAUTHORIZED);
    }
}
