<?php

declare(strict_types=1);

namespace App\Formatter\Snappy;

use App\Entity\Support\Manual;
use App\Formatter\PdfMerger;
use App\Formatter\Snappy\AdapterFactory\ManualChapter4ContentAdapterFactory;
use App\Formatter\Snappy\AdapterFactory\ManualChapter4CoverAdapterFactory;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

readonly class ManualChapter4PdfGenerator
{
    public function __construct(
        private FormatterInterface $pdfFormatter,
        private PdfMerger $pdfMerger,
    ) {
    }

    public function generateChapter4PdfFile(Manual $manual): File
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '120');

        $iso2 = $manual->language;
        $locale = null === $iso2 ? 'en' : mb_strtolower('CH' === $iso2 ? 'ZH' : $iso2);
        $cover = $this->pdfFormatter->convert($manual, ManualChapter4CoverAdapterFactory::PURPOSE, ManualChapter4CoverAdapterFactory::PURPOSE, ['manual_locale' => $locale]);
        $content = $this->pdfFormatter->convert($manual, ManualChapter4ContentAdapterFactory::PURPOSE, ManualChapter4ContentAdapterFactory::PURPOSE, ['manual_locale' => $locale]);

        $mergedPdf = $this->pdfMerger->merge([$cover, $content]);

        $fileSystem = new Filesystem();
        $tempfile = $fileSystem->tempnam(sys_get_temp_dir(), 'chapter4', '.pdf');
        $fileSystem->dumpFile($tempfile, $mergedPdf);

        return new File($tempfile);
    }

    public function getChapter4PdfResponse(Manual $manual): PdfResponse
    {
        $file = $this->generateChapter4PdfFile($manual);

        $response = new PdfResponse($file->getContent(), \sprintf('manual-chapter4-%07s.pdf', $manual->getId()), 'application/pdf', 'inline');

        $fileSystem = new Filesystem();
        $fileSystem->remove($file->getRealPath());

        return $response;
    }
}
