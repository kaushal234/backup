<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Modal
{
    public bool $onlyDialog = false;
    public bool $withCloseButton = false;
}
