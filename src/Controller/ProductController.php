<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends AbstractController
{
    #[Route('api/products', name: 'app_product', methods:['GET'])]
    public function getCollection(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $cacheId = 'getCollection' . $page . '_limit_' . $limit;

        $productList = $cachePool->get($cacheId, function (ItemInterface $item) use ($productRepository, $page, $limit) {
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
    public function getItem(int $id, SerializerInterface $serializer, ProductRepository $productRepository, TagAwareCacheInterface $cache): JsonResponse
    {
        $cacheId = 'getItem_' . $id;

        $product = $cache->get($cacheId, function (ItemInterface $item) use ($productRepository, $id) {
            $item->expiresAfter(3600);
            $item->tag('productCache');
            return $productRepository->find($id);
        });

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $jsonProduct = $serializer->serialize($product, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}

