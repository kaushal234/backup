<?php

declare(strict_types=1);

namespace App\AI\Extractor;

use PhpOffice\PhpWord\Element\AbstractContainer;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\IOFactory;

class DocxExtractor implements FileExtractorInterface
{
    public function supports(string $mime): bool
    {
        return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' === $mime;
    }

    public function extract(string $filepath): string
    {
        $phpWord = IOFactory::load($filepath);

        $output = '';
        foreach ($phpWord->getSections() as $section) {
            $output .= $this->extractElement($section);
        }

        return $output;
    }

    private function extractElement(object $element): string
    {
        if ($element instanceof Text) {
            return $element->getText();
        }

        if ($element instanceof TextBreak) {
            return "\n";
        }

        if ($element instanceof ListItem) {
            return '- '.$element->getTextObject()->getText()."\n";
        }

        if ($element instanceof Table) {
            $output = '';
            foreach ($element->getRows() as $row) {
                $cells = [];
                foreach ($row->getCells() as $cell) {
                    $cells[] = mb_trim($this->extractElement($cell));
                }
                $output .= implode("\t", $cells)."\n";
            }

            return $output."\n";
        }

        if ($element instanceof AbstractContainer) {
            $output = '';
            foreach ($element->getElements() as $child) {
                $output .= $this->extractElement($child);
            }

            return $output."\n";
        }

        return '';
    }
}
