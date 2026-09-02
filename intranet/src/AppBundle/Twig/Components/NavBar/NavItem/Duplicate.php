<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Duplicate', template: 'components/NavBar/NavItem.html.twig')]
class Duplicate extends NavItem
{
    public string $translationKey = 'nav_link.duplicate';

    public string $domain = 'messages';

    public string $icon = 'famicons:duplicate-outline';
}
