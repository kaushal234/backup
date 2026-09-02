<?php

declare(strict_types=1);

namespace App\AI\Service;

use App\AI\Extractor\FileExtractorInterface;
use App\Http\AzureDocumentIntelligenceClient;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Extracts plain text from a file. Used by UserMessageBuilder and
 * DocumentPlatform to send a file's textual content to the LLM instead
 * of a base64 data URL — much cheaper in tokens (a PDF/XLSX/DOCX
 * containing images can blow up to millions of tokens once
 * base64-encoded, while its extracted text is typically a few tens of
 * K).
 *
 * Dispatches to a format-specific FileExtractorInterface, then falls
 * back to Azure Document Intelligence when the native extraction
 * yields too little text (typically scanned documents) or fails.
 */
class FileTextExtractor
{
    private const int SCANNED_THRESHOLD = 50;

    /**
     * @param iterable<FileExtractorInterface> $extractors
     */
    public function __construct(
        #[AutowireIterator('app.file_extractor')]
        private readonly iterable $extractors,
        private readonly AzureDocumentIntelligenceClient $azureClient,
    ) {
    }

    public function extract(string $filepath): ?string
    {
        if (!is_file($filepath) || !is_readable($filepath)) {
            return null;
        }

        $mime = mime_content_type($filepath) ?: '';

        foreach ($this->extractors as $extractor) {
            if (!$extractor->supports($mime)) {
                continue;
            }

            try {
                $text = $extractor->extract($filepath);
                if (mb_strlen($text) >= self::SCANNED_THRESHOLD) {
                    return $text;
                }
            } catch (\Throwable) {
                // fall through to OCR
            }

            return $this->opticalCharacterRecognitionFallback($filepath);
        }

        return $this->opticalCharacterRecognitionFallback($filepath);
    }

    private function opticalCharacterRecognitionFallback(string $filepath): ?string
    {
        $content = file_get_contents($filepath);
        if (false === $content) {
            return null;
        }

        return $this->azureClient->doRequest($content);
    }
}
