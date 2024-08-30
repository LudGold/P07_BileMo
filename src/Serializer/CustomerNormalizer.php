<?php

namespace App\Serializer;

use App\Entity\Customer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CustomerNormalizer implements NormalizerInterface
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
        if (is_iterable($object)) {
            // Gestion de la collection de customers
            $data = [
                'items' => [],
                '_links' => [
                    'self' => $this->router->generate('get_customers', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    'add' => $this->router->generate('add_customer', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
                'pagination' => [
                    'current_page' => $context['pagination']['page'] ?? 1,
                    'items_per_page' => $context['pagination']['limit'] ?? 10,
                    'total_items' => $context['pagination']['total_items'] ?? count($object),
                    'total_pages' => $context['pagination']['total_pages'] ?? 1,
                ]
            ];

            foreach ($object as $customer) {
                if ($customer instanceof Customer) {
                    // Normaliser chaque customer avec les groupes de sérialisation
                    $customerData = $this->normalizer->normalize($customer, $format, array_merge($context, ['groups' => ['customer:read']]));
                    $customerData['_links'] = [
                        'self' => $this->router->generate('get_customer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                        'delete' => $this->router->generate('delete_customer', ['id' => $customer->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                    ];
                    $data['items'][] = $customerData;
                }
            }

            return $data;
        } elseif ($object instanceof Customer) {
            // Gestion d'un seul customer (item)
            $data = $this->normalizer->normalize($object, $format, array_merge($context, ['groups' => ['customer:read']]));

            
            $data['_links'] = [
                'self' => $this->router->generate('get_customer', ['id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'add' => $this->router->generate('add_customer', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'delete' => $this->router->generate('delete_customer', ['id' => $object->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            ];

            return $data;
        }

        throw new \InvalidArgumentException('The object must be iterable or an instance of Customer');
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
       
        return (is_iterable($data) && isset($context['collection_operation_name'])) ||
            ($data instanceof Customer && (!isset($context['collection_operation_name']) || isset($context['item_operation_name'])));
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'iterable<' . Customer::class . '>' => true,
            Customer::class => true,
        ];
    }
}
