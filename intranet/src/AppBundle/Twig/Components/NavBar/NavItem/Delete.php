<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\NavBar\NavItem;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'NavItem:Delete', template: 'components/NavBar/Modal.html.twig')]
class Delete extends Modal
{
    public string $id = 'nav_delete';

    public string $translationKey = 'nav_link.delete';

    public string $title = 'nav_link.delete';

    public string $body = 'modal_messages.confirm_delete';

    public string $leftButtonText = 'modal_messages.leftButtonText';

    public string $leftButtonIcon = 'material-symbols:close-rounded';

    public string $leftButtonColor = 'default';

    public string $rightButtonText = 'modal_messages.rightButtonText';

    public string $rightButtonIcon = 'material-symbols:check-rounded';

    public string $rightButtonColor = 'primary';

    public string $class = 'action-danger';

    public string $domain = 'messages';

    public string $icon = 'tabler:trash';
}
