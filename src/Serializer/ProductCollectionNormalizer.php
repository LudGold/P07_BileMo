<?php

namespace App\Serializer;

use App\Entity\Product;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductCollectionNormalizer implements NormalizerInterface
{
    private ObjectNormalizer $normalizer;
    private UrlGeneratorInterface $router;

    public function __construct(ObjectNormalizer $normalizer, UrlGeneratorInterface $router)
    {
        $this->normalizer = $normalizer;
        $this->router = $router;
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!is_iterable($object)) {
            throw new \InvalidArgumentException('The object must be iterable');
        }

        $data = [];
        foreach ($object as $item) {
            $itemData = $this->normalizer->normalize($item, $format, $context);

            // Ajout du lien self pour chaque produit
            $itemData['_links'] = [
                'self' => $this->router->generate('get_item', ['id' => $item->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ];

            $data[] = $itemData;
        }

        // Récupération des informations de pagination depuis le contexte
        $currentPage = $context['pagination']['page'] ?? 1;
        $itemsPerPage = $context['pagination']['limit'] ?? 10;
        $totalItems = $context['pagination']['total_items'] ?? count($data);
        $totalPages = $context['pagination']['total_pages'] ?? ceil($totalItems / $itemsPerPage);

        // Ajout des informations de pagination à la réponse
        $data['_meta'] = [
            'total_items' => $totalItems,
            'limit' => $itemsPerPage,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
        ];

        // Liens de pagination
        $data['_links'] = [
            'self' => $this->router->generate('get_collection', [
                'page' => $currentPage,
                'limit' => $itemsPerPage
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'prev' => $currentPage > 1
                ? $this->router->generate('get_collection', [
                    'page' => $currentPage - 1,
                    'limit' => $itemsPerPage
                ], UrlGeneratorInterface::ABSOLUTE_URL)
                : null,
            'next' => $currentPage < $totalPages
                ? $this->router->generate('get_collection', [
                    'page' => $currentPage + 1,
                    'limit' => $itemsPerPage
                ], UrlGeneratorInterface::ABSOLUTE_URL)
                : null,
        ];

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return is_iterable($data) && isset($context['collection_operation_name']);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Product::class . '[]' => true,
        ];
    }
}
