<?php

declare(strict_types=1);

namespace App\Jira\ResourceSourceProvider;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.jira.resource_source_provider')]
interface ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string;

    public function getCollectionReadOperation(): ?string;

    public function getWriteOperation(): ?string;

    public function getUrlOptions(array $context): array;

    public function supports(string $class): bool;

    public function getMainClass(): ?string;
}
