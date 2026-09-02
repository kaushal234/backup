<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PeoplePhoto
{
    public int $width = 39;
}
