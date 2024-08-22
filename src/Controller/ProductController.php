<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'get_collection', methods: ['GET'])]
    public function getCollection(
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cachePool
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 3);
        $page = max(1, $page);
        $limit = max(1, $limit);

        $cacheId = 'getCollection_page_' . $page . '_limit_' . $limit;

        $productList = $cachePool->get($cacheId, function (ItemInterface $item) use ($productRepository, $page, $limit) {
            $item->expiresAfter(3600);
            $item->tag('collectionCache');

            return $productRepository->findAllWithPagination($page, $limit);
        });
        $context = SerializationContext::create()->setGroups(['getCollection']);
        $jsonProductList = $serializer->serialize($productList, 'json', $context);

        return new JsonResponse(
            $jsonProductList,
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route('/api/products/{id}', name: 'get_item', methods: ['GET'])]
    public function getItem(
        int $id,
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        TagAwareCacheInterface $cache
    ): JsonResponse {
        $cacheId = 'getItem_' . $id;

        $product = $cache->get($cacheId, function (ItemInterface $item) use ($productRepository, $id) {
            $item->expiresAfter(3600);
            $item->tag('productCache');

            return $productRepository->find($id);
        });

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $context = SerializationContext::create()->setGroups(['getItem']);

        $jsonProduct = $serializer->serialize($product, 'json', $context);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
