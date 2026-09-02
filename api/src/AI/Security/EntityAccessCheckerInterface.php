<?php

declare(strict_types=1);

namespace App\AI\Security;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'ai.entity_access_checker')]
interface EntityAccessCheckerInterface
{
    public function supports(string $class): bool;

    public function isGranted(object $entity): bool;
}
