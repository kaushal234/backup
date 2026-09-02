<?php

declare(strict_types=1);

namespace App\ION\Client\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ComparisonExpression
{
    final public const ION_DEFAULT_COMPARISON_OPERATOR = 'eq';
    final public const ION_DEFAULT_LIKE_OPERATOR = 'like';
    final public const ION_COMPARISON_OPERATOR_LIST = [self::ION_DEFAULT_COMPARISON_OPERATOR, 'le', 'lt', 'ge', 'gt', 'ne', self::ION_DEFAULT_LIKE_OPERATOR];

    #[Assert\Choice(choices: self::ION_COMPARISON_OPERATOR_LIST)]
    public string $comparisonOperator;

    #[Assert\NotBlank]
    public string $instanceValue;

    #[Assert\NotBlank]
    public string $attributeName;

    public function __construct(string $comparisonOperator, string $instanceValue, string $attributeName)
    {
        $this->comparisonOperator = $comparisonOperator;
        $this->instanceValue = $instanceValue;
        $this->attributeName = $attributeName;
    }
}
