<?php

declare(strict_types=1);

namespace App\Factory\Common\Notification\Module;

use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\Common\Notification\NotificationTemplateList;
use App\Factory\Common\Notification\AbstractNotificationFactory;

class ThirdPartyAppUpdateTasksNotificationFactory extends AbstractNotificationFactory
{
    protected function getTemplateName(): string
    {
        return NotificationTemplateList::THIRD_PARTY_APP_UPDATE_TASKS_OPENED->value;
    }

    protected function getFormattedText(object $object, NotificationTemplate $notificationTemplate): string
    {
        return \sprintf($notificationTemplate->text, $object->getName());
    }

    protected function getRoute(object $object): string
    {
        return $this->urlGenerator->generate('third_party_app_update_task', ['id' => $object->getId()]);
    }
}
