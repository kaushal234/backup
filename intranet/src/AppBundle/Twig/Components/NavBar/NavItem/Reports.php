<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Reports', template: 'components/NavBar/NavItem.html.twig')]
class Reports extends NavItem
{
    public string $translationKey = 'nav_link.reports';

    public string $domain = 'messages';

    public string $icon = 'mynaui:list';
}
