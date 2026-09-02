<?php

declare(strict_types=1);

namespace App\Notifier\Tasks;

use App\Entity\Directory\People;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class ScheduledTaskNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {
    }

    public function sendExpirationEmail(array $scheduledTask, People $people): void
    {
        $email = (new TemplatedEmail())
            ->to($people->getEmail())
            ->addCc($people->getSupervisor()->getEmail())
            ->subject('scheduled_task.subject_expiration_notification')
            ->htmlTemplate('Emails/ScheduledTask/expiration.html.twig')
            ->context(['scheduledTask' => $scheduledTask]);

        $this->mailer->send($email);
    }
}
