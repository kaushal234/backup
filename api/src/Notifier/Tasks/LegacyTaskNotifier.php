<?php

declare(strict_types=1);

namespace App\Notifier\Tasks;

use App\Entity\Directory\People;
use LegacyBundle\Model\Task;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class LegacyTaskNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {
    }

    public function sendEmail(Task $task, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->to($task->getAssignee()->getEmail())
            ->addCc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $task->getCc()->toArray()))
            ->subject('task.task.subject_new')
            ->htmlTemplate('Emails/Task/new_task.html.twig')
            ->context($this->buildContext($task, $context));

        $this->mailer->send($email);
    }

    private function buildContext(Task $task, array $context = []): array
    {
        return $context + [
            'description' => $task->getDescription(),
            'taskId' => (string) $task->getId(),
            'assignee_name' => $task->getAssignee()->getDisplayName(),
            'assignor_name' => $task->getAssignor()->getDisplayName(),
        ];
    }
}
