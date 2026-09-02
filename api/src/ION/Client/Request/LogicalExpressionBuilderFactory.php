<?php

declare(strict_types=1);

namespace App\ION\Client\Request;

class LogicalExpressionBuilderFactory
{
    public function create(string $resourceName, string $operator = LogicalExpression::ION_LOGICAL_OPERATOR_AND): LogicalExpressionBuilder
    {
        return new LogicalExpressionBuilder($resourceName, $operator);
    }
}
