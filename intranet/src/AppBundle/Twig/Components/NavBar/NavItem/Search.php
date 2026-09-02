<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Search', template: 'components/NavBar/NavItem.html.twig')]
class Search extends NavItem
{
    public string $translationKey = 'nav_link.search';

    public string $domain = 'messages';

    public string $icon = 'material-symbols:search-rounded';
}
