<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ProductController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des smartphones Bilemo.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste complète des smartphones Bilemo",
     *     @OA\JsonContent(
     *        type="array",
     *     @Model(type=Product::class, groups={"getCollection"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     @OA\Property(description="La page que l'on veut récupérer")
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Tag(name="Products")
     * @Security(name="Bearer")
     */
    #[Route('/api/products', name: 'get_collection', methods: ['GET'])]
    public function getCollection(
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        Request $request,
        TagAwareCacheInterface $cachePool
    ): JsonResponse {
        //versionning api
        $acceptHeader = $request->headers->get('Accept');

        if ($acceptHeader === 'application/vnd.bilemo.v1+json') {
            $version = 'v1';
        } elseif ($acceptHeader === 'application/vnd.bilemo.v2+json') {
            $version = 'v2';
        } else {
            return new JsonResponse(['error' => 'Unsupported API version'], Response::HTTP_BAD_REQUEST);
        }


        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 3);
        $page = max(1, $page);
        $limit = max(1, $limit);

        $cacheId = 'getCollection_page_' . $page . '_limit_' . $limit . '_version_' . $version;;

        $productList = $cachePool->get($cacheId, function (ItemInterface $item) use ($productRepository, $page, $limit) {
            $item->expiresAfter(3600);
            $item->tag('collectionCache');

            return $productRepository->findAllWithPagination($page, $limit);
        });
        $serializationGroups = $version === 'v1' ? ['getCollection'] : ['getCollectionV2'];
        $jsonProductList = $serializer->serialize($productList, 'json', ['groups' => $serializationGroups]);

        return new JsonResponse(
            $jsonProductList,
            Response::HTTP_OK,
            [],
            true
        );
    }
    /**
     * Récupérer un produit spécifique par son ID.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne les détails d'un produit spécifique",
     *     @OA\JsonContent(ref=@Model(type=Product::class, groups={"getItem"}))
     * )
     * @OA\Tag(name="Products")
     * @Security(name="Bearer")
     */
    #[Route('/api/products/{id}', name: 'get_item', methods: ['GET'])]
    public function getItem(
        int $id,
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        TagAwareCacheInterface $cache,
        Request $request,
    ): JsonResponse {
        // Vérification de l'en-tête "Accept" pour déterminer la version de l'API
        $acceptHeader = $request->headers->get('Accept');

        if ($acceptHeader === 'application/vnd.bilemo.v1+json') {
            $version = 'v1';
        } elseif ($acceptHeader === 'application/vnd.bilemo.v2+json') {
            $version = 'v2';
        } else {
            return new JsonResponse(['error' => 'Unsupported API version'], Response::HTTP_BAD_REQUEST);
        }

        $cacheId = 'getItem_' . $id . '_version_' . $version;

        $product = $cache->get($cacheId, function (ItemInterface $item) use ($productRepository, $id) {
            $item->expiresAfter(3600);
            $item->tag('productCache');

            return $productRepository->find($id);
        });

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $serializationGroups = $version === 'v1' ? ['getItem'] : ['getItemV2'];
        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => $serializationGroups]);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
