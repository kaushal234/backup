<?php

declare(strict_types=1);

namespace App\ION\Client\Request;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class LogicalExpression
{
    final public const ION_LOGICAL_OPERATOR_OR = 'or';
    final public const ION_LOGICAL_OPERATOR_AND = 'and';

    #[Assert\Choice(choices: [self::ION_LOGICAL_OPERATOR_OR, self::ION_LOGICAL_OPERATOR_AND])]
    private string $logicalOperator;

    /**
     * @var ComparisonExpression[]
     */
    #[Assert\Valid]
    #[SerializedName('ComparisonExpression')]
    private array $comparisonExpressions;

    /**
     * @var LogicalExpression[]
     */
    #[Assert\Valid]
    #[SerializedName('LogicalExpression')]
    private array $logicalExpressions;

    public function __construct(string $logicalOperator)
    {
        $this->logicalOperator = $logicalOperator;
    }

    public function reset(): self
    {
        $this->comparisonExpressions = [];
        $this->logicalExpressions = [];
        $this->setLogicalOperatorAnd();

        return $this;
    }

    public function setLogicalOperatorAnd(): self
    {
        $this->logicalOperator = self::ION_LOGICAL_OPERATOR_AND;

        return $this;
    }

    public function setLogicalOperatorOr(): self
    {
        $this->logicalOperator = self::ION_LOGICAL_OPERATOR_OR;

        return $this;
    }

    public function getLogicalOperator(): string
    {
        return $this->logicalOperator;
    }

    public function addComparisonExpression(ComparisonExpression $comparisonExpression): self
    {
        $this->comparisonExpressions[] = $comparisonExpression;

        return $this;
    }

    /**
     * @return ComparisonExpression[]
     */
    public function getComparisonExpressions(): array
    {
        return $this->comparisonExpressions;
    }

    public function addLogicalExpression(self $logicalExpression): self
    {
        $this->logicalExpressions[] = $logicalExpression;

        return $this;
    }

    /**
     * @return LogicalExpression[]
     */
    public function getLogicalExpressions(): array
    {
        return $this->logicalExpressions;
    }
}
