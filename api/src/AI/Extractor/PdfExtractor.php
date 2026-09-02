<?php

declare(strict_types=1);

namespace App\AI\Extractor;

use Smalot\PdfParser\Parser as PdfParser;

class PdfExtractor implements FileExtractorInterface
{
    public function __construct(
        private readonly PdfParser $pdfParser,
    ) {
    }

    public function supports(string $mime): bool
    {
        return 'application/pdf' === $mime;
    }

    public function extract(string $filepath): string
    {
        $content = file_get_contents($filepath);
        if (false === $content) {
            throw new \RuntimeException(\sprintf('Unable to read file %s', $filepath));
        }

        return $this->pdfParser->parseContent($content)->getText();
    }
}
