<?php

declare(strict_types=1);

namespace App\AI\Service\Extractor;

use Alvest\FeatureDoc\Attribute\FeatureDoc;

#[FeatureDoc(path: 'ai-extractor.md')]
interface ExtractorInterface
{
    public function extract(string $class, array $uriVariables): string;
}
