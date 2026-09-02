<?php

declare(strict_types=1);

namespace App\ION\Filter\Warehousing;

use ApiPlatform\Serializer\Filter\FilterInterface;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Warehousing\Inventory;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\HttpFoundation\Request;

class InventoryWarehousesFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'includeInEnterprisePlanning';

    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        if (!$request->query->has(static::FILTER_PROPERTY)) {
            return;
        }

        if (Inventory::class !== $request->attributes->get('_api_resource_class')) {
            throw new \Exception('This filter is restricted to the Inventory resource');
        }

        $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] = $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] ?? [] + [
            self::FILTER_PROPERTY => IONXmlDecoder::convertBooleanToYesNo(\in_array($request->query->get(static::FILTER_PROPERTY), [true, 'true', '1'], true)),
        ];
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_PROPERTY => [
                'property' => static::FILTER_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
