<?php

declare(strict_types=1);

namespace App\AI\Resource;

use App\Entity\Sales\Product;
use Doctrine\ORM\EntityManagerInterface;
use Mcp\Capability\Attribute\McpResource;
use Symfony\Component\Serializer\SerializerInterface;

class ProductResource
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
    ) {
    }

    #[McpResource(
        uri: 'product://collection',
        name: 'products',
        description: 'List of products manufactured, produced or maintained by Alvest group.',
    )]
    public function getProducts(): array
    {
        $products = $this->em->getRepository(Product::class)->findBy(
            ['hidden' => false],
            ['name' => 'ASC'],
            20,
        );

        return [
            'uri' => 'product://collection',
            'mimeType' => 'application/json',
            'text' => $this->serializer->serialize(
                $products,
                'json',
                ['groups' => ['catalogue_product']]
            ),
        ];
    }
}
