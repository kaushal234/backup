<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class WidgetLink extends Widget
{
    public string $link;

    public array $linkParameters = [];

    public ?string $linkName = null;
}
