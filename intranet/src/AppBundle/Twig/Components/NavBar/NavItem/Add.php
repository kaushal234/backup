<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Add', template: 'components/NavBar/NavItem.html.twig')]
class Add extends NavItem
{
    public string $translationKey = 'nav_link.add';

    public string $domain = 'messages';

    public string $icon = 'bitcoin-icons:plus-filled';
}
