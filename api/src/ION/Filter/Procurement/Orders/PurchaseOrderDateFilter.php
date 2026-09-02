<?php

declare(strict_types=1);

namespace App\ION\Filter\Procurement\Orders;

use ApiPlatform\Doctrine\Common\Filter\DateFilterInterface;
use ApiPlatform\Serializer\Filter\FilterInterface;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use Symfony\Component\HttpFoundation\Request;

class PurchaseOrderDateFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'orderDate';

    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        if (!$request->query->has(static::FILTER_PROPERTY)) {
            return;
        }

        if (PurchaseOrder::class !== $request->attributes->get('_api_resource_class')) {
            throw new \Exception('This filter is restricted to the PurchaseOrder resource');
        }

        $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] = $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] ?? [];
        $dateFilters = $request->query->all(static::FILTER_PROPERTY);
        foreach ($dateFilters as $attribute => $dateFilter) {
            if (!\in_array($attribute, [DateFilterInterface::PARAMETER_AFTER, DateFilterInterface::PARAMETER_BEFORE], true)) {
                continue;
            }
            $context[DataAreaFilter::CONTEXT_DATA_AREA_KEY] += [\sprintf('orderDate%s', ucfirst($attribute)) => (new \DateTime($dateFilter))->format(\DATE_ATOM)];
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];
        foreach ([DateFilterInterface::PARAMETER_AFTER, DateFilterInterface::PARAMETER_BEFORE] as $attribute) {
            $description[\sprintf('orderDate[%s]', $attribute)] = [
                'property' => 'orderDate',
                'type' => \DateTimeInterface::class,
                'required' => false,
            ];
        }

        return $description;
    }
}
