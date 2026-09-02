<?php

declare(strict_types=1);

namespace App\ION\Filter\Warehousing\Shipments;

use ApiPlatform\Serializer\Filter\FilterInterface;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Warehousing\Shipments\OrderReference;
use App\ION\Resources\Warehousing\Shipments\Shipment;
use Symfony\Component\HttpFoundation\Request;

class ShipmentOrderFilter implements FilterInterface
{
    final public const string FILTER_PROPERTY = 'order';

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $context
     *
     * @throws \Exception
     */
    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        if (!$request->query->has(static::FILTER_PROPERTY)) {
            return;
        }

        if (Shipment::class !== $request->attributes->get('_api_resource_class')) {
            throw new \Exception('This filter is restricted to the Shipment resource');
        }

        $context += self::generateContext((string) $request->query->get(static::FILTER_PROPERTY));
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_PROPERTY => [
                'property' => static::FILTER_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function generateContext(string $orderNumber): array
    {
        return [
            DataAreaFilter::CONTEXT_DATA_AREA_KEY => [
                'order' => $orderNumber,
                'orderOrigins' => implode('|', OrderReference::SALES_ORDER_ORIGINS),
            ],
        ];
    }
}
