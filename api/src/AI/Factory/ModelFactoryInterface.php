<?php

declare(strict_types=1);

namespace App\AI\Factory;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'ai.model_factory')]
#[FeatureDoc(path: 'ai-extractor.md')]
interface ModelFactoryInterface
{
    public function supports(string $class): bool;

    public function create(object $entity): object;
}
