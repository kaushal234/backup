<?php

declare(strict_types=1);

namespace App\Link\ResourceSourceProvider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.link.resource_source_provider')]
interface ResourceSourceProviderInterface
{
    public function getService(): string;

    public function getProperties(): string;

    public function getReturnedFields(): string;

    public function supports(string $class): bool;
}
