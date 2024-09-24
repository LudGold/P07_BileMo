<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Serializer\CustomerNormalizer;
use App\Service\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    // Route pour obtenir la liste des clients
    #[Route('/api/customers', name: 'get_customers', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // Vérifie que l'utilisateur a le rôle ROLE_USER
    public function getCustomers(
        CustomerRepository $customerRepository,
        CustomerNormalizer $customerNormalizer,
        Request $request,
        CacheService $cacheService
    ): JsonResponse {

        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie que l'utilisateur est authentifié
        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur non authentifié');
        }

        // Récupère les paramètres de pagination depuis la requête HTTP
        $page = max(1, $request->get('page', 1)); // Page courante, par défaut 1
        $limit = max(1, $request->get('limit', 6)); // Nombre d'éléments par page, par défaut 6

        // Génère un identifiant de cache unique pour cette requête
        $cacheId = 'getCustomers_user' . $user->getId() . '_page' . $page . '_limit' . $limit;

        // Calcule le nombre total d'éléments pour la pagination
        $totalItems = $customerRepository->count(['user' => $user]); // Nombre total de clients pour cet utilisateur
        $totalPages = $totalItems > 0 ? ceil($totalItems / $limit) : 1; // Nombre total de pages

        // Utilisation du service de cache pour récupérer ou stocker les données en cache
        $customerList = $cacheService->getCacheData(
            $cacheId,
            function () use ($customerRepository, $user, $page, $limit) {
                // Cette fonction est exécutée si les données ne sont pas présentes dans le cache
                return $customerRepository->findAllWithPagination($user, $page, $limit);
            },
            ['customerCache'] // Tags du cache pour permettre l'invalidation ultérieure
        );

        // Contexte de sérialisation pour normaliser les données
        $context = [
            'groups' => ['customer:read'], // Groupes de sérialisation définis dans les annotations des entités
            'collection_operation_name' => 'getCollection', // Indique qu'il s'agit d'une opération de collection
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ],
        ];

        // Normalise la liste des clients en un tableau associatif
        $jsonCustomerList = $customerNormalizer->normalize($customerList, null, $context);
        // Ajoute un message si la liste des clients est vide
        if (empty($jsonCustomerList) || !isset($jsonCustomerList['items']) || empty($jsonCustomerList['items'])) {
            $jsonCustomerList = [
                'items' => [],
                'message' => 'No customers found for this user.',
                'links' => [
                    'self' => $request->getUri(), // URL de la requête actuelle
                    'add' => $this->generateUrl('add_customer') // URL pour ajouter un nouveau client, vous pouvez remplacer par votre route
                ],
                'pagination' => [
                    'page' => $page,
                    'items_per_page' => $limit,
                    'total_items' => $totalItems,
                    'total_pages' => $totalPages,
                    'prev' => $page > 1 ? $this->generateUrl('get_customers', ['page' => $page - 1, 'limit' => $limit]) : null,
                    'next' => $page < $totalPages ? $this->generateUrl('get_customers', ['page' => $page + 1, 'limit' => $limit]) : null,
                ]
            ];
        }
        // Convertit les données normalisées en JSON
        $jsonData = json_encode($jsonCustomerList);

        // Retourne la réponse JSON avec les données des clients
        return new JsonResponse(
            $jsonData,
            Response::HTTP_OK,
            [],
            true // Indique que $jsonData est déjà encodé en JSON
        );
    }

    // Route pour obtenir un client spécifique
    #[Route('/api/customers/{id}', name: 'get_customer', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // Vérifie que l'utilisateur a le rôle ROLE_USER
    public function getCustomer(
        int $id,
        CustomerRepository $customerRepository,
        SerializerInterface $serializer,
        CacheService $cacheService
    ): JsonResponse {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie que l'utilisateur est authentifié
        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur non authentifié');
        }

        // Génère un identifiant de cache unique pour ce client
        $cacheId = 'getCustomer_' . $id;

        // Utilisation du service de cache pour récupérer ou stocker le client en cache
        $customer = $cacheService->getCacheData(
            $cacheId,
            function () use ($customerRepository, $id) {
                // Cette fonction est exécutée si les données ne sont pas présentes dans le cache
                return $customerRepository->find($id);
            },
            ['customerCache'] // Tags du cache pour permettre l'invalidation ultérieure
        );

        // Vérifie que le client existe et appartient à l'utilisateur connecté
        if (!$customer || $customer->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('Client non trouvé ou accès refusé');
        }

        // Sérialise le client en JSON
        $jsonCustomer = $serializer->serialize(
            $customer,
            'json',
            [
                'groups' => 'customer:read',
                'item_operation_name' => true,
            ]
        );

        // Retourne la réponse JSON avec les données du client
        return new JsonResponse(
            $jsonCustomer,
            Response::HTTP_OK,
            [],
            true // Indique que $jsonCustomer est déjà encodé en JSON
        );
    }

    // Route pour ajouter un nouveau client
    #[Route('/api/customers', name: 'add_customer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')] // Vérifie que l'utilisateur a le rôle ROLE_USER
    public function addCustomer(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CacheService $cacheService,
    ): JsonResponse {
        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie que l'utilisateur est authentifié
        if (!$user instanceof User) {
            throw new BadRequestHttpException('Utilisateur non authentifié');
        }

        // Désérialise les données JSON reçues en un objet Customer
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        // Initialise les attributs non fournis par le client
        $customer->setCreatedAt(new \DateTimeImmutable()); // Date de création
        $customer->setUser($user); // Associe le client à l'utilisateur connecté

        // Valide l'entité Customer
        $errors = $validator->validate($customer);
        if (count($errors) > 0) {
            // Retourne les erreurs de validation au client
            return new JsonResponse((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        // Persiste et enregistre le client dans la base de données
        $entityManager->persist($customer);
        $entityManager->flush();

        // Invalide le cache lié aux clients de l'utilisateur
        $cacheService->invalidateCache(['customerCache_user' . $user->getId()]);

        // Sérialise le client en JSON pour la réponse
        $jsonCustomer = $serializer->serialize(
            $customer,
            'json',
            [
                'groups' => 'customer:read',
                'item_operation_name' => true,
            ]
        );

        // Retourne la réponse JSON avec les données du nouveau client
        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, [], true);
    }

    // Route pour supprimer un client
    #[Route('/api/customers/{id}', name: 'delete_customer', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')] // Vérifie que l'utilisateur a le rôle ROLE_USER
    public function deleteCustomer(
        int $id,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager,
        CacheService $cacheService
    ): JsonResponse {
        // Récupère le client à supprimer
        $customer = $customerRepository->find($id);

        // Vérifie que le client existe
        if (!$customer) {
            throw new NotFoundHttpException('Client non trouvé');
        }

        // Récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie que l'utilisateur est authentifié
        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur non authentifié');
        }

        // Vérifie que le client appartient bien à l'utilisateur connecté
        if ($customer->getUser()->getId() !== $user->getId()) {
            return new JsonResponse([
                'error' => 'Vous n\'avez pas la permission de supprimer ce client.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Supprime le client de la base de données
        $entityManager->remove($customer);
        $entityManager->flush();

        // Invalide le cache lié aux clients
        $cacheService->invalidateCache(['customerCache']);

        // Retourne une réponse vide avec le statut HTTP 204 (No Content)
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
