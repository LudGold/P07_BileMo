<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;


class UserController extends AbstractController
{
    #[Route('/api/customers', name: 'get_customers', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getCustomers(
        CustomerRepository $customerRepository,
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            throw new NotFoundHttpException('User not authenticated');
        }

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 6);
        $cacheId = 'getCustomers_' . $page . 'limit' . $limit;

        $customerList = $cache->get($cacheId, function (ItemInterface $item) use ($customerRepository, $page, $limit) {
            $item->expiresAfter(3600);
            $item->tag('customerCache');

            return $customerRepository->findAllWithPagination($page, $limit);
        });

        $jsonCustomerList = $serializer->serialize($customerList, 'json', ['groups' => 'customer:read', 'collection_operation_name' => true]);

        return new JsonResponse(
            $jsonCustomerList,
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
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cacheId = 'getCustomer_' . $id;

        $customer = $cache->get($cacheId, function (ItemInterface $item) use ($customerRepository, $id) {
            $item->expiresAfter(3600);
            $item->tag('customerCache');

            return $customerRepository->find($id);
        });

        if (!$customer) {
            throw new NotFoundHttpException('Customer not found');
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
    ValidatorInterface $validator
): JsonResponse {
    // Vérifier que l'utilisateur est authentifié
    $user = $this->getUser();
    if (!$user) {
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

    // Sérialiser et retourner la réponse
   $jsonCustomer = $serializer->serialize($customer, 'json', [
    'groups' => 'customer:read',
    'item_operation_name' => true // Ajoutez cette clé pour activer le normalizer
]);
    return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, [], true);
}


    #[Route('/api/customers/{id}', name: 'delete_customer', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteCustomer(
        int $id,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            throw new NotFoundHttpException('Customer not found');
        }
        $entityManager->remove($customer);
        $entityManager->flush();

        $cache->invalidateTags(['customerCache']);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
