<?php

declare(strict_types=1);

namespace App\Factory\Common\Notification\Quality;

use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\Common\Notification\NotificationTemplateList;
use App\Factory\Common\Notification\AbstractNotificationFactory;

class DerogationDecisionUpdateNotificationFactory extends AbstractNotificationFactory
{
    protected function getTemplateName(): string
    {
        return NotificationTemplateList::DEROGATION_DECISION_UPDATE->value;
    }

    protected function getFormattedText(object $object, NotificationTemplate $notificationTemplate): string
    {
        return \sprintf('Derogation#%s: %s', $object->getId(), $notificationTemplate->text);
    }

    protected function getRoute(object $object): string
    {
        return $this->urlGenerator->generate('derogation', ['id' => $object->getId()]);
    }
}
