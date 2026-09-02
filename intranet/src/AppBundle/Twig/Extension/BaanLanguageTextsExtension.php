<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class BaanLanguageTextsExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('baan_language_texts', [$this, 'formatBaanTexts'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
        ];
    }

    public function formatBaanTexts(array $texts): string
    {
        $text = array_reduce($texts, static function ($memo, $textSequence) {
            return $memo.$textSequence['text'];
        }, '');

        return nl2br(htmlspecialchars($text));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'baan_language_texts';
    }
}
