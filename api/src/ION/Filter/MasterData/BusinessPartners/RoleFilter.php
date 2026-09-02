<?php

declare(strict_types=1);

namespace App\ION\Filter\MasterData\BusinessPartners;

use ApiPlatform\Serializer\Filter\FilterInterface;
use App\ION\Client\Request\ComparisonExpression;
use App\ION\Client\Request\LogicalExpression;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\Filter\IONFilter;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\SourceProvider\SourceProvider;
use Symfony\Component\HttpFoundation\Request;

class RoleFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'role';

    public function __construct(
        private readonly LogicalExpressionBuilderFactory $logicalExpressionBuilderFactory,
        private readonly SourceProvider $sourceProvider
    ) {
    }

    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        if (!$request->query->has(static::FILTER_PROPERTY)) {
            return;
        }

        if (BusinessPartner::class !== $request->attributes->get('_api_resource_class')) {
            throw new \Exception('This filter is restricted to the BusinessPartner resource');
        }

        $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider(BusinessPartner::class);

        if (null === $ionResource = $resourceSourceProvider->getResource()) {
            return;
        }

        $logicalExpressionBuilder = $this->logicalExpressionBuilderFactory->create($ionResource, LogicalExpression::ION_LOGICAL_OPERATOR_OR);

        $logicalExpressionBuilder
            ->addCondition(
                ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR,
                $request->query->get(static::FILTER_PROPERTY),
                'role'
            )
            ->addCondition(
                ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR,
                'both',
                'role'
            )
        ;

        /** @var LogicalExpression $logicalExpression */
        $logicalExpression = $context[IONFilter::CONTEXT_LOGICAL_EXPRESSION_KEY];
        $logicalExpression->addLogicalExpression($logicalExpressionBuilder->getLogicalExpression());
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
}
