<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ContactTooltipBox
{
    public string $placement = 'top';
    public int $delay = 300;
}
