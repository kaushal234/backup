<?php

declare(strict_types=1);

namespace App\Validator\Constraints\MIS\Module;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ModuleNotification extends Constraint
{
    public function __construct(
        public string $messageKeyUser = 'A Key User must be defined if notifyOperationalOwner is disabled.',
        public string $messageLocalKeyUsers = 'At least one Local Key User must be defined if notifyKeyUser is disabled.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
