<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem', template: 'components/NavBar/NavItem.html.twig')]
class NavItem
{
    public string $translationKey;

    public string $domain;

    public string $target = '_self';
    public string $icon;

    public string $route;

    public array $routeParameters = [];

    public bool $security = true;

    public string $class = '';
}
