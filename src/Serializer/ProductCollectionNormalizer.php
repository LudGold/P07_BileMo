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

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        if (!is_iterable($object)) {
            throw new \InvalidArgumentException('The object must be iterable');
        }

        $data = [];
        foreach ($object as $item) {
            $data[] = $this->normalizer->normalize($item, $format, $context);
        }

        // Pagination
        $data['_meta'] = [
            'total_items' => count($data),
            'limit' => $context['limit'] ?? 10,
            'offset' => $context['offset'] ?? 0,
            'current_page' => $context['page'] ?? 1,
            'total_pages' => ceil(count($data) / ($context['limit'] ?? 10)),
        ];

        // Ajouter des liens pour filtrer par marque, type, etc.
        $data['_links'] = [
            'self' => $this->router->generate('get_products', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'by_brand' => $this->router->generate('get_products_by_brand', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'by_type' => $this->router->generate('get_products_by_type', [], UrlGeneratorInterface::ABSOLUTE_URL),
            // Ajoutez d'autres liens si nécessaire
        ];

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
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
