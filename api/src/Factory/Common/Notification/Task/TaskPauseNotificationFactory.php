<?php

declare(strict_types=1);

namespace App\Factory\Common\Notification\Task;

use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\Common\Notification\NotificationTemplateList;
use App\Entity\Task\Task;
use App\Factory\Common\Notification\AbstractNotificationFactory;

class TaskPauseNotificationFactory extends AbstractNotificationFactory
{
    protected function getTemplateName(): string
    {
        return NotificationTemplateList::TASK_PAUSE->value;
    }

    /**
     * @param Task $object
     */
    protected function getFormattedText(object $object, NotificationTemplate $notificationTemplate): string
    {
        return \sprintf('%s#%s: %s %s.', $notificationTemplate->module->getName(), $object->getId(), $notificationTemplate->text, $object->dueDate->format('Y-m-d'));
    }

    protected function getRoute(object $object): string
    {
        return $this->urlGenerator->generate('task', ['id' => $object->getId()]);
    }
}
