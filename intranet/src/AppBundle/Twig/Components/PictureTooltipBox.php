<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PictureTooltipBox
{
    public string $placement = 'left';
    public int $delay = 300;
}
