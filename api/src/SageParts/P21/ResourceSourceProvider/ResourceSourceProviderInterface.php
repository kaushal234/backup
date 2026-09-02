<?php

declare(strict_types=1);

namespace App\SageParts\P21\ResourceSourceProvider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.sage_p21.resource_source_provider')]
interface ResourceSourceProviderInterface
{
    public function getOperation(): ?string;

    public function getFieldMapping(): array;

    public function supports(string $class): bool;
}
