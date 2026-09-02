<?php

declare(strict_types=1);

namespace App\AI\Extractor;

class PlainTextExtractor implements FileExtractorInterface
{
    public function supports(string $mime): bool
    {
        return str_starts_with($mime, 'text/');
    }

    public function extract(string $filepath): string
    {
        $content = file_get_contents($filepath);
        if (false === $content) {
            throw new \RuntimeException(\sprintf('Unable to read file %s', $filepath));
        }

        return $content;
    }
}
