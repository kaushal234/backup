<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Modal', template: 'components/NavBar/Modal.html.twig')]
class Modal extends NavItem
{
    public string $id;

    public string $title;

    public string $size = 'lg';

    public ?string $name = null;
    public string $body = '';

    public string $role = 'document';

    public string $leftButtonText = 'modal_messages.leftButtonText';

    public string $leftButtonIcon = 'material-symbols:close-rounded';

    public string $leftButtonColor = 'default';

    public string $rightButtonText = 'modal_messages.rightButtonText';

    public string $rightButtonIcon = 'material-symbols:check-rounded';

    public string $rightButtonColor = 'primary';

    public string $class = '';
}
