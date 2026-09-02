<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PeopleTooltipBox
{
    public string $placement = 'right';
    public int $delay = 300;
}
