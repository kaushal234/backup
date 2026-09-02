<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\Dropdown;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Dropdown', template: 'components/NavBar/Dropdown.html.twig')]
class Dropdown
{
    public string $translationKey;

    public string $domain;

    public bool $security = true;

    public string $class = '';
}
