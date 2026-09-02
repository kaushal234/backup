<?php

declare(strict_types=1);

namespace App\AI\Service\Extractor;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('ai.extractor.custom')]
#[FeatureDoc(path: 'ai-extractor.md')]
interface CustomExtractorInterface extends ExtractorInterface
{
    public function supports(string $class): bool;
}
