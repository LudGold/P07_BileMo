<?php

namespace App\Serializer;

use App\Entity\Product;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductItemNormalizer implements NormalizerInterface
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
        if (!$object instanceof Product) {
            throw new \InvalidArgumentException('The object must be an instance of Product');
        }

        $data = $this->normalizer->normalize($object, $format, $context);

        // Ajout de liens spécifiques pour le produit
        $data['_links'] = [
            'self' => $this->router->generate('get_item', ['id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'collection' => $this->router->generate('get_collection', [], UrlGeneratorInterface::ABSOLUTE_URL),

        ];

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        // Vérifie si l'objet est une instance de Product et si c'est une opération item
        return $data instanceof Product && (!isset($context['collection_operation_name']) || isset($context['item_operation_name']));
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Product::class => true,
        ];
    }
}
