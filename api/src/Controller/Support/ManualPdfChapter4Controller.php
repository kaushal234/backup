<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Entity\Support\Manual;
use App\Formatter\Snappy\ManualChapter4PdfGenerator;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

class ManualPdfChapter4Controller
{
    public function __construct(
        private readonly ManualChapter4PdfGenerator $manualChapter4PdfGenerator,
    ) {
    }

    public function __invoke(Manual $manual): PdfResponse
    {
        return $this->manualChapter4PdfGenerator->getChapter4PdfResponse($manual);
    }
}
