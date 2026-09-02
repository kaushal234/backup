<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Entity\Support\Manual;
use App\Formatter\Snappy\AdapterFactory\ManualProductNumberListAdapterFactory;
use App\Formatter\Snappy\FormatterInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class ManualPdfPartsListController
{
    public function __construct(
        private readonly FormatterInterface $pdfFormatter
    ) {
    }

    public function __invoke(Manual $manual): PdfResponse
    {
        $content = $this->pdfFormatter->convert($manual, ManualProductNumberListAdapterFactory::PURPOSE, ManualProductNumberListAdapterFactory::PURPOSE);

        return new PdfResponse($content, \sprintf('manual-pnref-%07s.pdf', $manual->getId()), 'application/pdf', 'inline');
    }
}
