<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Classe responsable de l'authentification JWT dans l'application.
 */
class JWTAuthenticator extends AbstractAuthenticator
{
    private $jwtManager;
    private $userProvider;

    /**
     * Constructeur de la classe JWTAuthenticator.
     * 
     * @param JWTTokenManagerInterface $jwtManager Service de gestion des tokens JWT.
     * @param UserProviderInterface $userProvider Service de gestion des utilisateurs.
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, UserProviderInterface $userProvider)
    {
        $this->jwtManager = $jwtManager;
        $this->userProvider = $userProvider;
    }

    /**
     * Vérifie si la requête contient un jeton d'authentification dans l'en-tête.
     * 
     * @param Request $request La requête HTTP.
     * @return bool|null Retourne true si le jeton est présent, sinon false.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    /**
     * Authentifie l'utilisateur basé sur le jeton JWT fourni.
     * 
     * @param Request $request La requête HTTP.
     * @return Passport Le passeport contenant les informations d'authentification de l'utilisateur.
     * 
     * @throws CustomUserMessageAuthenticationException Si le jeton est invalide ou si l'utilisateur n'est pas trouvé.
     */
    public function authenticate(Request $request): Passport
    {
        // Extraction du token de l'en-tête 'Authorization'.
        $token = str_replace('Bearer ', '', $request->headers->get('Authorization'));

        try {
            // Analyse du token pour extraire les informations de l'utilisateur.
            $payload = $this->jwtManager->parse($token);
        } catch (\Exception $e) {
            // Exception levée si le token est invalide.
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        // Récupération du nom d'utilisateur à partir du payload du token.
        $username = $payload['username'] ?? null;

        if (null === $username) {
            // Exception levée si le nom d'utilisateur est manquant.
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }

        // Création d'un passeport auto-validant avec le UserBadge.
        return new SelfValidatingPassport(new UserBadge($username, function ($userIdentifier) {
            // Charge l'utilisateur par son identifiant.
            return $this->userProvider->loadUserByIdentifier($userIdentifier);
        }));
    }

    /**
     * Gestion de l'échec de l'authentification.
     * 
     * @param Request $request La requête HTTP.
     * @param AuthenticationException $exception L'exception d'authentification levée.
     * @return Response La réponse HTTP avec un message d'erreur.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Retourne une réponse JSON avec un message d'erreur et le statut HTTP 401.
        return new JsonResponse(['error' => $exception->getMessageKey()], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Gestion du succès de l'authentification.
     * 
     * @param Request $request La requête HTTP.
     * @param TokenInterface $token Le token d'authentification.
     * @param string $firewallName Le nom du pare-feu utilisé.
     * @return Response|null Retourne null pour permettre à la requête de continuer.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Aucune action nécessaire en cas de succès de l'authentification.
        // Laisse la requête continuer normalement.
        return null;
    }

    /**
     * Gère la demande d'authentification pour les utilisateurs non authentifiés.
     * 
     * @param Request $request La requête HTTP.
     * @param AuthenticationException|null $authException L'exception d'authentification (le cas échéant).
     * @return Response La réponse HTTP avec un message d'erreur indiquant que l'authentification est requise.
     */
    public function start(Request $_request, ?AuthenticationException $_authException = null): Response
    {
        // Retourne une réponse JSON indiquant que l'authentification est requise, avec le statut HTTP 401.
        return new JsonResponse(['error' => 'Authentication Required'], Response::HTTP_UNAUTHORIZED);
    }
}
