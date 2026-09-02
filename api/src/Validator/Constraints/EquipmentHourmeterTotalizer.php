<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EquipmentHourmeterTotalizer extends Constraint
{
    public function __construct(
        public readonly string $minMessage = 'The hourmeter totalizer calculated is {{ value }} but should be superior or equal to {{ limit }}.',
        public readonly string $maxMessage = 'The hourmeter totalizer calculated is {{ value }} but should be inferior to {{ limit }}.',
        public readonly ?string $errorPath = null,
        public readonly mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(
            options: $options,
            groups: $groups,
            payload: $payload,
        );
    }

    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function getRequiredOptions(): array
    //    {
    //        return ['errorPath'];
    //    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
