<?php

declare(strict_types=1);

namespace App\SageParts\ResourceSourceProvider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.sage.resource_source_provider')]
interface ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string;

    public function supports(string $class): bool;
}
