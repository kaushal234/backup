<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:NewTask', template: 'components/NavBar/NavItem.html.twig')]
class NewTask extends NavItem
{
    public string $translationKey = 'nav_link.new_task';

    public string $domain = 'messages';

    public string $icon = 'bitcoin-icons:plus-filled';
}
