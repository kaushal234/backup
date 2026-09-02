<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('HtmlTooltipBox')]
class HtmlTooltipBox
{
    public ?string $html = null;
    public int $length = 200;
    public string $placement = 'top';

    public function hasContent(): bool
    {
        return '' !== $this->getPlain();
    }

    public function getShort(): string
    {
        $plain = $this->getPlain();

        if ('' === $plain) {
            return '';
        }

        if (mb_strlen($plain) > $this->length) {
            return mb_substr($plain, 0, $this->length).'…';
        }

        return $plain;
    }

    private function getPlain(): string
    {
        if (null === $this->html) {
            return '';
        }

        $plain = strip_tags($this->html);

        return trim($plain);
    }
}
