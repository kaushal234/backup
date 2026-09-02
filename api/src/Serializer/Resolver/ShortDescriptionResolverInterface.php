<?php

declare(strict_types=1);

namespace App\Serializer\Resolver;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[FeatureDoc(path: 'short-description-resolver.md')]
#[AutoconfigureTag('app.short_description_resolver')]
interface ShortDescriptionResolverInterface
{
    public function supports(object $resource): bool;

    public function resolve(object $resource): ?string;
}
