<?php

declare(strict_types=1);

namespace App\Entity\Common\Notification;

enum NotificationTemplateList: string
{
    case TROUBLE_TICKET_ASSIGNED = 'trouble_ticket_assigned';
    case THIRD_PARTY_APP_UPDATE_TASKS_OPENED = 'third_party_app_update_tasks_opened';
    case DEROGATION_DECISION_UPDATE = 'derogation_decision_update';
    case TROUBLE_TICKET_CLOSED = 'trouble_ticket_closed';
    case TROUBLE_TICKET_AWAIT_USER = 'trouble_ticket_await_user';
    case TASK_ASSIGNED = 'task_assigned';
    case TASK_CLOSED = 'task_closed';
    case TASK_RESCHEDULED = 'task_rescheduled';
    case TASK_PAUSE = 'task_pause';
}
