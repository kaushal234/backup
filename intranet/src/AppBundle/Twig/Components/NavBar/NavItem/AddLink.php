<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:AddLink', template: 'components/NavBar/NavItem.html.twig')]
class AddLink extends NavItem
{
    public string $translationKey = 'nav_link.add_link';

    public string $domain = 'messages';

    public string $icon = 'bitcoin-icons:plus-filled';
}
