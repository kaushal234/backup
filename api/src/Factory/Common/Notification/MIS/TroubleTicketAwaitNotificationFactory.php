<?php

declare(strict_types=1);

namespace App\Factory\Common\Notification\MIS;

use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\Common\Notification\NotificationTemplateList;
use App\Factory\Common\Notification\AbstractNotificationFactory;

class TroubleTicketAwaitNotificationFactory extends AbstractNotificationFactory
{
    protected function getTemplateName(): string
    {
        return NotificationTemplateList::TROUBLE_TICKET_AWAIT_USER->value;
    }

    protected function getFormattedText(object $object, NotificationTemplate $notificationTemplate): string
    {
        return \sprintf('%s#%s: %s', $notificationTemplate->module->getName(), $object->getId(), $notificationTemplate->text);
    }

    protected function getRoute(object $object): string
    {
        return $this->urlGenerator->generate('trouble_ticket', ['id' => $object->getId()]);
    }
}
