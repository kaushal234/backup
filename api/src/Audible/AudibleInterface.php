<?php

declare(strict_types=1);

namespace App\Audible;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.audible')]
interface AudibleInterface
{
    public function supports(string $type): bool;

    public function getClass(): string;

    public function getAudibleProperties(): array;
}
