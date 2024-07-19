<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('api/customers', name: 'app_customer_list', methods: ['GET'])]
    // récupérer la liste des clients
    public function getCustomers(CustomerRepository $customerRepository, SerializerInterface $serializer, request $request): JsonResponse
    {
        
        $page = $request->get('page',1);
        $limit = $request->get('limit', 3);
        $customerList = $customerRepository->findAllWithPagination($page, $limit);

        $context = SerializationContext::create()->setGroups(['customer:read']);
        $jsonCustomerList = $serializer->serialize($customerList, 'json', $context);
        return new JsonResponse(
            $jsonCustomerList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('api/customers/{id<\d+>}', name: 'app_customer_get', methods: ['GET'])]
    // récupérer un seul client
    public function getCustomer(int $id, CustomerRepository $customerRepository, SerializerInterface $serializer): JsonResponse
    {
        $customer = $customerRepository->find($id);
        $context = SerializationContext::create()->setGroups(['customer:read']);
        if (!$customer) {
            return new JsonResponse(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonCustomer = $serializer->serialize($customer, 'json', $context);
        return new JsonResponse(
            $jsonCustomer,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('api/customers', name: 'app_customer_add', methods: ['POST'])]
    // pour ajouter un nouveau client
    public function addCustomer(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator, UserRepository $userRepository): JsonResponse
    {
        $customer=$serializer->deserialize($request->getContent(), Customer::class, 'json');
        $customer->setcreatedAt(date_create_immutable());
        //user mis en dur avant gestion authentification user
        $user = $userRepository->findOneByEmail('ivory16@wisozk.com');
        $customer->setUser($user);
        $errors = $validator->validate($customer);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse($errorsString, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($customer);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize($customer, 'json'),
            Response::HTTP_CREATED,
            [],
            true
        );
    }

    #[Route('api/customers/{id<\d+>}', name: 'app_customer_delete', methods: ['DELETE'])]
    // pour supprimer un client
    public function deleteCustomer(int $id, CustomerRepository $customerRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if ($customer) {
            $entityManager->remove($customer);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Customer deleted'], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Customer not found'], Response::HTTP_NOT_FOUND);
    }
}
