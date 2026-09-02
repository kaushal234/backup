<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.ion.resource_source_provider')]
#[FeatureDoc(path: 'ion-source-provider.md')]
interface ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string;

    public function getCollectionReadOperation(): ?string;

    public function getResource(): ?string;

    public function getUpdateOperation(): string|array|null;

    public function getCreateOperation(): string|array|null;

    public function getDataAreaFilter(): array;

    public function deserializeAfterPersist(): bool;

    public function getRestItemReadOperation(array $uriVariables = []): ?string;

    public function getRestCollectionReadOperation(array $uriVariables = []): ?string;

    public function getFilters(): array;

    public function supports(string $class): bool;
}
