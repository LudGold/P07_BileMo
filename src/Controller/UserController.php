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
    #[Route('/api/customers', name: 'get_customers', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getCustomers(
        CustomerRepository $customerRepository,
        CustomerNormalizer $customerNormalizer,
        Request $request,
        CacheService $cacheService
    ): JsonResponse {

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not authenticated');
        }

        $page = max(1, $request->get('page', 1));
        $limit = max(1, $request->get('limit', 6));
        $cacheId = 'getCustomers_user' . $user->getId() . '_page' . $page . '_limit' . $limit;

        // Calcul du nombre total d'éléments pour la pagination
        $totalItems = $customerRepository->count(['user' => $user]); // Nombre total de clients
        $totalPages = $totalItems > 0 ? ceil($totalItems / $limit) : 1;

        // Utilisation du CacheService
        $customerList = $cacheService->getCacheData($cacheId, function () use ($customerRepository, $user, $page, $limit) {
            return $customerRepository->findAllWithPagination($user, $page, $limit);
        }, ['customerCache']);

        // Contexte de sérialisation
        $context = [
            'groups' => ['customer:read'],
            'collection_operation_name' => 'getCollection',
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ],
        ];

        // Utilisation du normalizer directement
        $jsonCustomerList = $customerNormalizer->normalize($customerList, null, $context);
        // Convertir les données normalisées en JSON
        $jsonData = json_encode($jsonCustomerList);
        return new JsonResponse(
            $jsonData,
            Response::HTTP_OK,
            [],
            true
        );
    }


    #[Route('/api/customers/{id}', name: 'get_customer', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getCustomer(
        int $id,
        CustomerRepository $customerRepository,
        SerializerInterface $serializer,
        CacheService $cacheService
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not authenticated');
        }
        $cacheId = 'getCustomer_' . $id;

        // Utilisation du CacheService
        $customer = $cacheService->getCacheData($cacheId, function () use ($customerRepository, $id) {
            return $customerRepository->find($id);
        }, ['customerCache']);

        if (!$customer || $customer->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException('Customer not found or access denied');
        }

        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'customer:read', 'item_operation_name' => true]);

        return new JsonResponse(
            $jsonCustomer,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/customers', name: 'add_customer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addCustomer(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CacheService $cacheService,
    ): JsonResponse {
        // Vérifier que l'utilisateur est authentifié
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new BadRequestHttpException('User not authenticated');
        }

        // Désérialiser les données reçues en objet Customer
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        // Initialiser les attributs non fournis par le client
        $customer->setCreatedAt(new \DateTimeImmutable());
        $customer->setUser($user);

        // Validation de l'entité
        $errors = $validator->validate($customer);
        if (count($errors) > 0) {
            return new JsonResponse((string) $errors, Response::HTTP_BAD_REQUEST);
        }

        // Persister et enregistrer le Customer
        $entityManager->persist($customer);
        $entityManager->flush();
        $cacheService->invalidateCache(['customerCache_user' . $user->getId()]);

        // Sérialiser et retourner la réponse
        $jsonCustomer = $serializer->serialize($customer, 'json', [
            'groups' => 'customer:read',
            'item_operation_name' => true,
        ]);

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/customers/{id}', name: 'delete_customer', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteCustomer(
        int $id,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager,
        CacheService $cacheService
    ): JsonResponse {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            throw new NotFoundHttpException('Customer not found');
        }
        $user = $this->getUser();

        // Vérifie que le customer appartient bien à l'utilisateur connecté
        if (!$user instanceof User) {
            throw new NotFoundHttpException('User not authenticated');
        }
        if ($customer->getUser()->getId() !== $user->getId()) {
            return new JsonResponse([
                'error' => 'You do not have permission to delete this customer.'
            ], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($customer);
        $entityManager->flush();

        $cacheService->invalidateCache(['customerCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
