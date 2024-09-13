<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CacheService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
     *        @Model(type=Product::class, groups={"getCollection"})
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     @OA\Property(description="La page que l'on veut récupérer"),
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
        CacheService $cacheService
    ): JsonResponse {
        // Versionning API
        $version = $request->attributes->get('api_version');

        // Pagination
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 3);
        $page = max(1, $page);
        $limit = max(1, $limit);

        // Générer un cacheId unique pour la requête
        $cacheId = 'getCollection_page_'.$page.'_limit_'.$limit.'_version_'.$version;

        // Utilisation du service de cache
        $productList = $cacheService->getCacheData($cacheId, function () use ($productRepository, $page, $limit) {
            return $productRepository->findAllWithPagination($page, $limit);
        }, ['collectionCache'], 3600);

        // Détermination des groupes de sérialisation en fonction de la version de l'API
        $serializationGroups = 'v1' === $version ? ['getCollection'] : ['getCollectionV2'];
        $jsonProductList = $serializer->serialize($productList, 'json', ['groups' => $serializationGroups]);

        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
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
        CacheService $cacheService,
        Request $request
    ): JsonResponse {
        // Versionning API
        $version = $request->attributes->get('api_version');

        // Générer un cacheId unique pour la requête
        $cacheId = 'getItem_'.$id.'_version_'.$version;

        // Utilisation du service de cache
        $product = $cacheService->getCacheData($cacheId, function () use ($productRepository, $id) {
            $product = $productRepository->find($id);
            if (!$product) {
                throw new NotFoundHttpException('Product not found');
            }
            return $product;
        }, ['productCache'], 3600);

        // Détermination des groupes de sérialisation en fonction de la version de l'API
        $serializationGroups = 'v1' === $version ? ['getItem'] : ['getItemV2'];
        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => $serializationGroups]);

        return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    }
}
