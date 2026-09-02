<?php

declare(strict_types=1);

namespace AppBundle\Controller\Manufacturing;

use ApiBundle\Client;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/manufacturing', defaults: ['alvest_module' => 'RRR'])]
class ProductManufacturingController extends AbstractController
{
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '/product-manufacturing', name: 'product_manufacturing_list', methods: ['GET'])]
    #[Template('manufacturing/product_manufacturing/list.html.twig')]
    public function list()
    {
        $factories = $this->client->findBy('locations', ['capability.factory' => true], ['name' => 'asc'], ['raw_results' => true]);
        $products = $this->client->findBy('sales/products',
            [
                'hidden' => false,
                'normalization_groups_override' => ['product_manufacturing'],
                'pagination' => false,
            ],
            ['name' => 'ASC'],
            ['raw_results' => true]
        );

        $products = array_reduce((array) ($products['hydra:member'] ?? []), static function ($memo, $product) use ($factories) {
            foreach ($product['productManufacturings'] as $key => $productManufacturing) {
                foreach ([(new \DateTime('last year'))->format('Y'), (new \DateTime())->format('Y'), (new \DateTime('next year'))->format('Y')] as $year) {
                    if ($year === (new \DateTime($productManufacturing['effectiveAt']))->format('Y')) {
                        $newKey = \sprintf('%s-%s-%s', $productManufacturing['factory']['@id'], $product['@id'], (new \DateTime($productManufacturing['effectiveAt']))->format('Y'));
                        $product['productManufacturings'][$newKey] = $productManufacturing;
                        unset($product['productManufacturings'][$key]);
                        continue 2;
                    }
                    unset($product['productManufacturings'][$key]);
                }
            }

            foreach ($factories['hydra:member'] as $factory) {
                foreach ([(new \DateTime())->format('Y'), (new \DateTime('next year'))->format('Y')] as $year) {
                    $key = \sprintf('%s-%s-%s', $factory['@id'], $product['@id'], $year);
                    if (!\array_key_exists($key, $product['productManufacturings'])) {
                        $product['productManufacturings'][$key] = [];
                    }
                }
            }

            $memo[] = $product;

            return $memo;
        }, []);

        return [
            'initialState' => [
                'product' => [
                    'products' => $products,
                ],
                'location' => [
                    'factories' => $factories['hydra:member'],
                ],
            ],
        ];
    }
}
