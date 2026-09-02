<?php

declare(strict_types=1);

namespace App\Tests\Formatter;

use App\Formatter\PdfMerger;
use PHPUnit\Framework\TestCase;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class PdfMergerTest extends TestCase
{
    public function testMergeConcatenatesEveryPageOfEverySource(): void
    {
        $merged = (new PdfMerger())->merge([
            $this->createPdf(2),
            $this->createPdf(3),
        ]);

        self::assertStringStartsWith('%PDF-', $merged);
        self::assertSame(5, $this->countPages($merged));
    }

    public function testMergeKeepsThePageSizeAndOrientationOfEachSource(): void
    {
        $merged = (new PdfMerger())->merge([
            $this->createPdf(1),
            $this->createPdf(1, 'L'),
        ]);

        $pdf = new Fpdi();
        $pdf->setSourceFile(StreamReader::createByString($merged));

        $portrait = $pdf->getTemplateSize($pdf->importPage(1));
        $landscape = $pdf->getTemplateSize($pdf->importPage(2));

        self::assertSame('P', $portrait['orientation']);
        self::assertSame('L', $landscape['orientation']);
        self::assertEqualsWithDelta($portrait['width'], $landscape['height'], 0.01);
        self::assertEqualsWithDelta($portrait['height'], $landscape['width'], 0.01);
    }

    public function testMergeOfASingleDocumentPreservesItsPageCount(): void
    {
        $merged = (new PdfMerger())->merge([$this->createPdf(4)]);

        self::assertSame(4, $this->countPages($merged));
    }

    public function testMergeWithoutAnySourceReturnsAnEmptyDocument(): void
    {
        $merged = (new PdfMerger())->merge([]);

        self::assertStringStartsWith('%PDF-', $merged);
        self::assertSame(1, $this->countPages($merged));
    }

    public function testMergeFailsOnAnInvalidSource(): void
    {
        $this->expectException(\Exception::class);

        (new PdfMerger())->merge(['not a pdf']);
    }

    private function createPdf(int $pageCount, string $orientation = 'P'): string
    {
        $pdf = new Fpdi($orientation);
        $pdf->SetFont('Helvetica');

        for ($page = 1; $page <= $pageCount; ++$page) {
            $pdf->AddPage();
            $pdf->Cell(40, 10, \sprintf('Page %d', $page));
        }

        return $pdf->Output('S');
    }

    private function countPages(string $content): int
    {
        return (new Fpdi())->setSourceFile(StreamReader::createByString($content));
    }
}
