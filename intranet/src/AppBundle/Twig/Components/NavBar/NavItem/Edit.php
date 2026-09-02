<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Edit', template: 'components/NavBar/NavItem.html.twig')]
class Edit extends NavItem
{
    public string $translationKey = 'nav_link.edit';

    public string $domain = 'messages';

    public string $class = 'action-warning';

    public string $icon = 'material-symbols:edit-outline-rounded';
}
