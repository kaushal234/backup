<?php

declare(strict_types=1);

namespace App\ION\Filter;

use ApiPlatform\Serializer\Filter\FilterInterface;
use ApiPlatform\State\Util\RequestParser;
use App\ION\Client\Request\ComparisonExpression;
use App\ION\Client\Request\LogicalExpression;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\SourceProvider\SourceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\TypeInfo\TypeIdentifier;

class IONFilter implements FilterInterface
{
    /** @var string */
    final public const ION_LOGICAL_OPERATOR_FILTER = 'logicalOperator';

    /** @var string */
    final public const CONTEXT_LOGICAL_EXPRESSION_KEY = '_ion_logical_expression';

    public function __construct(
        private readonly LogicalExpressionBuilderFactory $logicalExpressionBuilderFactory,
        private readonly SourceProvider $sourceProvider,
        private readonly array $properties = [])
    {
    }

    public function apply(Request $request, bool $normalization, array $attributes, array &$context): void
    {
        if (!$request->attributes->has('_api_resource_class')) {
            return;
        }

        $resourceClass = $request->attributes->get('_api_resource_class');

        try {
            $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($resourceClass);
        } catch (UnprocessableEntityHttpException $exception) {
            return;
        }

        if (null === ($ionResource = $resourceSourceProvider->getResource())) {
            return;
        }

        $queryString = RequestParser::getQueryString($request);
        $queryParameters = $queryString ? RequestParser::parseRequestParams($queryString) : [];

        if ([] === array_intersect_key($this->properties, $queryParameters)) {
            return;
        }

        /** @var LogicalExpression $logicalExpression */
        $logicalExpression = $context[static::CONTEXT_LOGICAL_EXPRESSION_KEY];
        $logicalExpressionBuilder = $this->logicalExpressionBuilderFactory->create($ionResource);
        $logicalExpressionBuilder->setLogicalExpression($logicalExpression);

        foreach ($this->properties as $property => $ionProperty) {
            if (isset($queryParameters[$property])) {
                $propertyValues = (array) $queryParameters[$property];
                /** @var string|int $comparisonOperator */
                foreach ($propertyValues as $comparisonOperator => $values) {
                    $comparisonOperator = \is_string($comparisonOperator) ? $comparisonOperator : ComparisonExpression::ION_DEFAULT_COMPARISON_OPERATOR;
                    foreach ((array) $values as $value) {
                        $logicalExpressionBuilder->addCondition($comparisonOperator, $value, $ionProperty ?? $property);
                    }
                }
            }
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [
            static::ION_LOGICAL_OPERATOR_FILTER => [
                'property' => static::ION_LOGICAL_OPERATOR_FILTER,
                'type' => TypeIdentifier::STRING->value,
                'required' => false,
            ],
        ];

        foreach (array_keys($this->properties) as $property) {
            foreach (ComparisonExpression::ION_COMPARISON_OPERATOR_LIST as $operator) {
                $paths = [
                    $property,
                    \sprintf('%s[%s]', $property, $operator),
                    \sprintf('%s[%s][]', $property, $operator),
                ];
                foreach ($paths as $path) {
                    $propertyPath = \sprintf($path, $property, $operator);
                    $description[$propertyPath] = [
                        'property' => $propertyPath,
                        'type' => TypeIdentifier::STRING->value,
                        'required' => false,
                    ];
                }
            }
        }

        return $description;
    }
}
