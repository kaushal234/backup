<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'LegacyNavItem', template: 'components/NavBar/LegacyNavItem.html.twig')]
class LegacyNavItem
{
    public string $translationKey;

    public string $url;

    public ?string $icon = null;

    public bool $security = true;

    public string $class = '';

    public string $target = '_self';
}
