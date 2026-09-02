<?php

declare(strict_types=1);

namespace App\ION\Client\Request;

final class LogicalExpressionBuilder
{
    private readonly string $resourceName;
    private LogicalExpression $logicalExpression;

    public function __construct(string $resourceName, string $operator = LogicalExpression::ION_LOGICAL_OPERATOR_AND)
    {
        $this->resourceName = $resourceName;
        $this->logicalExpression = new LogicalExpression($operator);
    }

    public function setLogicalExpression(LogicalExpression $logicalExpression): self
    {
        $this->logicalExpression = $logicalExpression;

        return $this;
    }

    public function addCondition(string $comparisonOperator, string $instanceValue, string $attributeName): self
    {
        $this->logicalExpression->addComparisonExpression(new ComparisonExpression(
            $comparisonOperator,
            $instanceValue,
            \sprintf('%s.%s', $this->resourceName, $attributeName))
        );

        return $this;
    }

    public function getLogicalExpression(): LogicalExpression
    {
        return $this->logicalExpression;
    }
}
