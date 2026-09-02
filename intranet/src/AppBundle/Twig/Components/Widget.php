<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Widget
{
    public string $icon = '';
    public int $iconHeight = 40;
    public int $iconWidth = 40;
    public string $translationKey;
    public string $domain;
    public ?string $value = '';
}
