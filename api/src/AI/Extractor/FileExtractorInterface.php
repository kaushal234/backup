<?php

declare(strict_types=1);

namespace App\AI\Extractor;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[FeatureDoc(path: 'ai-file-extractor.md')]
#[AutoconfigureTag('app.file_extractor')]
interface FileExtractorInterface
{
    public function supports(string $mime): bool;

    /**
     * Extract plain text from $filepath. May return an empty or short
     * string (e.g. scanned PDF with no embedded text); the dispatcher
     * decides whether to apply a fallback.
     *
     * @throws \Throwable when the file cannot be parsed
     */
    public function extract(string $filepath): string;
}
