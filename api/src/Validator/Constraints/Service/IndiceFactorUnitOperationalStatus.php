<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class IndiceFactorUnitOperationalStatus extends Constraint
{
    public function __construct(
        public readonly string $message = 'toc.messages.errors.indice_factor',
    ) {
        parent::__construct();
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
