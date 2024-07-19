<?php

namespace App\Controller;

use App\Repository\ProductRepository;

use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route('api/products', name: 'app_product', methods:['GET'])]
    //pour récuperer la liste des produits
    public function getCollection(ProductRepository $productRepository, SerializerInterface $serializer, request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page',1);
        $limit = $request->get('limit', 3);
        $cacheId = 'getCollection'. $page. '_limit_'. $limit;

        $productList = $cachePool->get($cacheId, function (ItemInterface $item) use ($productRepository, $page, $limit){
            echo('l\'element pas encore en cache');
            $item->expiresAfter(3600);
            $item->tag('collectionCache');
            return $productRepository->findAllWithPagination($page, $limit);
        });
        

        $jsonProductList = $serializer->serialize($productList, 'json');
        return new JsonResponse(
            $jsonProductList,
            Response::HTTP_OK,
            [],
            true
        );
    }
    #[Route('api/products/{id}', name: 'app_one_product', methods: ['GET'])]
    // pour récupérer un produit
    public function getItem(int $id, SerializerInterface $serializer, ProductRepository $productRepository, TagAwareCacheInterface $cache): JsonResponse
    {
        $cacheId = 'getItem_' . $id;

        $product = $cache->get($cacheId, function (ItemInterface $item) use ($productRepository, $id) {
            $item->expiresAfter(3600);
            $item->tag('productCache');
            return $productRepository->find($id);
        });

        if ($product) {
            $jsonProduct = $serializer->serialize($product, 'json');
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(
            ['message' => 'Product not found'],
            Response::HTTP_NOT_FOUND
        );
    }
}