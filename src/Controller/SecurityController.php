<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SecurityController extends AbstractController
{
    // Services nécessaires pour l'authentification et la génération de jetons JWT
    private $jwtManager;
    private $userProvider;
    private $passwordHasher;

    /**
     * Constructeur pour injecter les dépendances nécessaires :
     * - JWTTokenManagerInterface : Pour générer des jetons JWT.
     * - UserProviderInterface : Pour charger les utilisateurs à partir du système de gestion des utilisateurs.
     * - UserPasswordHasherInterface : Pour vérifier la validité des mots de passe.
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, UserProviderInterface $userProvider, UserPasswordHasherInterface $passwordHasher)
    {
        $this->jwtManager = $jwtManager;
        $this->userProvider = $userProvider;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Route pour l'authentification des utilisateurs.
     * - Méthode POST à l'URL /api/login.
     * 
     * Cette méthode reçoit un email et un mot de passe, vérifie les informations 
     * et renvoie un jeton JWT en cas de succès.
     */
    #[Route(path: '/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // Récupère le contenu JSON de la requête et le décode en tableau associatif.
        $data = json_decode($request->getContent(), true);
        
        // Récupère les champs 'email' et 'password' du tableau, ou null s'ils ne sont pas présents.
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        // Vérifie si l'email et le mot de passe sont fournis.
        if (!$email || !$password) {
            throw new BadRequestHttpException('Email and password are required');
        }

        // Charge l'utilisateur à partir de l'email fourni.
        $user = $this->userProvider->loadUserByIdentifier($email);
        
        // Vérifie que l'utilisateur existe et que le mot de passe est valide.
        if (!$user instanceof PasswordAuthenticatedUserInterface || !$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Génère un jeton JWT pour l'utilisateur authentifié.
        $token = $this->jwtManager->create($user);

        // Retourne le jeton JWT dans la réponse JSON.
        return new JsonResponse(['token' => $token]);
    }
}
