<?php

namespace App\Controller;

use App\Repository\ProductRepository;

use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('api/products', name: 'app_product')]
    //pour récuperer la liste des produits
    public function getCollection(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        $productList = $productRepository->findAll();

        $jsonProductList = $serializer->serialize($productList, 'json');
        return new JsonResponse(
            $jsonProductList,
            Response::HTTP_OK,
            [],
            true
        );
    }
    #[Route('api/products/{id}', name: 'app_one_product', methods:['GET'])]
    //pour récuperer un produit
    public function getItem(int $id, SerializerInterface $serializer,ProductRepository $productRepository ){
    {
        $product = $productRepository->find($id);
        if($product){
            $jsonProduct = $serializer->serialize($product, 'json');
            return new JsonResponse($jsonProduct,Response::HTTP_OK,[],true
            );
        }
        return new JsonResponse(
            ['message' => 'Product not found'],
            Response::HTTP_NOT_FOUND);
            
}}
}