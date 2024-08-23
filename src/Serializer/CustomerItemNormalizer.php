<?php

namespace App\Serializer;

use App\Entity\Customer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerItemNormalizer implements NormalizerInterface
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
        
        if (!$object instanceof Customer) {
            throw new \InvalidArgumentException('The object must be an instance of Customer');
        }
       
        $data = $this->normalizer->normalize($object, $format, $context);

        // Ajout de liens spécifiques pour le client
        $data['_links'] = [
            'self' => $this->router->generate('get_customer', ['id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'delete' => $this->router->generate('delete_customer', ['id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        return $data;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        // Vérifie si l'objet est une instance de Customer et si c'est une opération item
        return $data instanceof Customer && (!isset($context['collection_operation_name']) || isset($context['item_operation_name']));
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Customer::class => true,
        ];
    }
}
