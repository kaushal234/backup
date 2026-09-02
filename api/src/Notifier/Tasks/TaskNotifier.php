<?php

declare(strict_types=1);

namespace App\Notifier\Tasks;

use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Entity\Task\Task;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TaskNotifier
{
    public function __construct(
        private MailerInterface $mailer,
        private NormalizerInterface $normalizer,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmail(Task $task, string $value, array $context = []): void
    {
        $ccs = array_map(static fn (People $recipient) => $recipient->getEmail(), $task->getRecipients()->toArray());
        foreach ($ccs as $key => $cc) {
            if ($cc === $task->assignee->getEmail()) {
                unset($ccs[$key]);
            }
        }

        $email = (new TemplatedEmail())
            ->to($task->assignee->getEmail())
            ->addCc(...$ccs)
            ->subject(\sprintf('task.task.%s', $value))
            ->htmlTemplate(\sprintf('Emails/Task/%s.html.twig', $value))
            ->context($this->buildContext($task, $context));

        $this->mailer->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendTaskAutoEscalatedEmail(Task $task, string $value, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->to($task->assignee->getSupervisor()->getEmail())
            ->addCc($task->assignee->getEmail())
            ->subject(\sprintf('task.task.%s', $value))
            ->htmlTemplate(\sprintf('Emails/Task/%s.html.twig', $value))
            ->context($this->buildContext($task, $context));

        $this->mailer->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendBadSurveyEmail(Task $task, TechnicianOnCallSurvey $survey): void
    {
        $recipients = array_map(
            static fn (People $recipient) => $recipient->getEmail(),
            $task->getRecipients()->toArray(),
        );

        $email = (new TemplatedEmail())
            ->to($task->assignee->getEmail())
            ->addCc(...$recipients)
            ->subject($this->translator->trans('toc.subject.task_bad_survey', ['%id%' => $task->getId()], 'emails'))
            ->htmlTemplate('Emails/Service/TechnicianOnCall/bad_survey.html.twig')
            ->context(['task' => $task, 'survey' => $survey]);

        $this->mailer->send($email);
    }

    private function buildContext(Task $task, array $context = []): array
    {
        return $context + [
            'task' => $this->normalizer->normalize($task, null, [
                'groups' => ['base_task', 'task', 'task:item', 'module_light', 'people_public', 'location_public', 'task:comment'],
            ]),
            'id' => (string) $task->getId(),
            'user_full_name' => $context['user'] ?? null,
            'assignee_name' => $task->assignee->getDisplayName(),
            'createdBy_name' => $task->createdBy->getDisplayName(),
            'supervisor_name' => $task->assignee->getSupervisor()?->getDisplayName(),
        ];
    }
}
