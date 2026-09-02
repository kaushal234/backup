<?php

declare(strict_types=1);

namespace App\ION\Filter\Procurement\Orders;

use ApiPlatform\Serializer\Filter\FilterInterface;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use Symfony\Component\HttpFoundation\Request;

class PurchaseOrderOpenFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'open';

    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        if (!$request->query->has(static::FILTER_PROPERTY)) {
            return;
        }

        if (PurchaseOrder::class !== $request->attributes->get('_api_resource_class')) {
            throw new \Exception('This filter is restricted to the PurchaseOrder resource');
        }

        if (\in_array($request->query->get(static::FILTER_PROPERTY), [true, 'true', '1'], true)) {
            $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] = $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] ?? [];
            $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] += ['status' => implode('|', PurchaseOrder::OPEN_STATUSES)];
        }
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
