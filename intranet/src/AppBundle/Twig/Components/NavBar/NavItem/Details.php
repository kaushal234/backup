<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Details', template: 'components/NavBar/NavItem.html.twig')]
class Details extends NavItem
{
    public string $translationKey = 'nav_link.details';

    public string $domain = 'messages';

    public string $icon = 'mynaui:list';
}
