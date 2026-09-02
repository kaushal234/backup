<?php

declare(strict_types=1);

namespace App\Formatter;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

class PdfMerger
{
    /**
     * @param string[] $pdfContents
     */
    public function merge(array $pdfContents): string
    {
        $pdf = new Fpdi();

        foreach ($pdfContents as $content) {
            $pageCount = $pdf->setSourceFile(
                StreamReader::createByString($content)
            );

            for ($page = 1; $page <= $pageCount; ++$page) {
                $template = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($template);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

                $pdf->useTemplate($template);
            }
        }

        return $pdf->Output('S');
    }
}
