<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Files', template: 'components/NavBar/NavItem.html.twig')]
class Files extends NavItem
{
    public string $translationKey = 'nav_link.files';

    public string $domain = 'messages';

    public string $icon = 'pepicons-pencil:file';
}
