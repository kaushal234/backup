<?php

declare(strict_types=1);

namespace App\ION\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use ApiPlatform\State\Util\RequestParser;
use App\ION\Client\Request\LogicalExpression;
use App\ION\Client\Request\LogicalExpressionBuilderFactory;
use App\ION\Filter\IONFilter;
use App\ION\SourceProvider\SourceProvider;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MainLogicalExpressionContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly SourceProvider $sourceProvider,
        private readonly LogicalExpressionBuilderFactory $logicalExpressionBuilderFactory)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (null === ($resourceClass = $request->attributes->get('_api_resource_class'))) {
            return $context;
        }

        try {
            $resourceSourceProvider = $this->sourceProvider->getResourceSourceProvider($resourceClass);
        } catch (UnprocessableEntityHttpException $exception) {
            return $context;
        }

        if (null === ($ionResource = $resourceSourceProvider->getResource())) {
            return $context;
        }

        $queryString = RequestParser::getQueryString($request);
        $queryParameters = $queryString ? RequestParser::parseRequestParams($queryString) : [];
        $logicalOperator = $queryParameters[IONFilter::ION_LOGICAL_OPERATOR_FILTER] ?? LogicalExpression::ION_LOGICAL_OPERATOR_AND;

        if (!\in_array($logicalOperator, [LogicalExpression::ION_LOGICAL_OPERATOR_OR, LogicalExpression::ION_LOGICAL_OPERATOR_AND], true)) {
            throw new BadRequestException(\sprintf('Value %s is not valid for filter %s', $logicalOperator, IONFilter::ION_LOGICAL_OPERATOR_FILTER));
        }

        $logicalExpressionBuilder = $this->logicalExpressionBuilderFactory->create($ionResource, $logicalOperator);

        $context[IONFilter::CONTEXT_LOGICAL_EXPRESSION_KEY] = $logicalExpressionBuilder->getLogicalExpression();

        return $context;
    }
}
