<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Entity\Support\ManualDocument;
use App\Formatter\Snappy\AdapterFactory\ManualDocumentAdapterFactory;
use App\Formatter\Snappy\FormatterInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class ManualDocumentPdfController
{
    public function __construct(
        private readonly FormatterInterface $pdfFormatter
    ) {
    }

    public function __invoke(ManualDocument $manualDocument): PdfResponse
    {
        $content = $this->pdfFormatter->convert($manualDocument, ManualDocumentAdapterFactory::PURPOSE, ManualDocumentAdapterFactory::PURPOSE);

        return new PdfResponse($content, \sprintf('manual-document-%07s.pdf', $manualDocument->getId()), 'application/pdf', 'inline');
    }
}
