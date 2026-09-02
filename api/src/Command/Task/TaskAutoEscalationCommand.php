<?php

declare(strict_types=1);

namespace App\Command\Task;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Task\Task;
use App\Notifier\Tasks\TaskNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'api:mis:notify_escalated_task',
    description: 'Auto-escalate tasks: compare escalationDate & rescheduleDate, advance by trigger.'
)]
class TaskAutoEscalationCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
        private readonly TaskNotifier $notifier,
    ) {
        parent::__construct();
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $now = new \DateTimeImmutable('now');

        $tasks = $this->entityManager->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.status <> :closed and t.status <> :pause')
            ->setParameter('closed', Task::CLOSED)
            ->setParameter('pause', Task::PAUSE)
            ->getQuery()
            ->getResult();

        $logger->info(\sprintf('%d tasks selected', \count($tasks)));
        $escalated = 0;

        /** @var Task $task */
        foreach ($tasks as $task) {
            // Must have a supervisor to escalate to
            $supervisor = $task->assignee?->getSupervisor();
            if (null === $supervisor) {
                $logger->info(\sprintf('Task %d skipped: no supervisor.', $task->getId()));
                continue;
            }

            // Compute the scheduled next escalation
            $scheduledNext = $task->escalationDate instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($task->escalationDate)
                : self::addInterval(
                    \DateTimeImmutable::createFromInterface($task->dueDate),
                    $task->escalationTrigger,
                    $task->escalationTriggerUnit
                );

            // If rescheduleDate is set, it’s a "not before" anchor: take the later of the two
            if ($task->rescheduleDate instanceof \DateTimeInterface) {
                $rescheduleDate = \DateTimeImmutable::createFromInterface($task->rescheduleDate);
                $nextDue = max($scheduledNext, $rescheduleDate);
            } else {
                $nextDue = $scheduledNext;
            }

            $logger->info(\sprintf(
                'Task %d nextDue=%s (scheduledNext=%s%s).',
                $task->getId(),
                $nextDue->format('Y-m-d H:i:s'),
                $scheduledNext->format('Y-m-d H:i:s'),
                $task->rescheduleDate instanceof \DateTimeInterface
                    ? ', reschedule='.$task->rescheduleDate->format('Y-m-d H:i:s')
                    : ''
            ));

            if ($nextDue > $now) {
                $logger->info(\sprintf('Task %d not due yet, skipping.', $task->getId()));
                continue;
            }

            // Escalate
            ++$escalated;
            $logger->info(\sprintf('Task %d escalating.', $task->getId()));

            $comment = (new Comment())
                ->setMessage(\sprintf(
                    'This task has been automatically escalated from %s to %s, who is now the new assignee',
                    $task->assignee,
                    $supervisor
                ))
                ->setResource($this->iriConverter->getIriFromResource($task));
            $this->entityManager->persist($comment);
            $logger->info(\sprintf('Task %d comment added.', $task->getId()));

            $this->notifier->sendTaskAutoEscalatedEmail($task, 'escalated');
            $logger->info(\sprintf('Task %d email sent.', $task->getId()));

            $task->assignee = $supervisor;
            $logger->info(\sprintf('Task %d new assignee: %s.', $task->getId(), $task->assignee));

            // Advance the next window by the task’s trigger
            $task->escalationDate = self::addInterval(
                $nextDue,
                $task->escalationTrigger,
                $task->escalationTriggerUnit
            );
            $logger->info(\sprintf(
                'Task %d new escalationDate: %s.',
                $task->getId(),
                $task->escalationDate->format('Y-m-d H:i:s')
            ));

            $this->entityManager->persist($task);
        }

        $this->entityManager->flush();
        $logger->info(\sprintf('%d tasks escalated', $escalated));

        return Command::SUCCESS;
    }

    private static function addInterval(\DateTimeInterface $base, int $n, string $unit): \DateTime
    {
        $date = $base instanceof \DateTime
            ? clone $base
            : \DateTime::createFromInterface($base);

        $interval = match (mb_strtoupper($unit)) {
            'DAYS' => new \DateInterval("P{$n}D"),
            'WEEKS' => new \DateInterval("P{$n}W"),
            'MONTHS' => new \DateInterval("P{$n}M"),
            'YEARS' => new \DateInterval("P{$n}Y"),
            default => new \DateInterval("P{$n}D"),
        };

        return $date->add($interval);
    }
}
