<?php

declare(strict_types=1);

namespace App\Command\Task;

use App\Entity\BaseTask;
use App\Entity\MIS\GuestUser\GuestUser;
use App\Entity\Module\Module;
use App\Entity\Task\RenewGuestUser;
use App\Repository\MIS\GuestUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'api:guest-user:create-renewal-tasks',
    description: 'Create renewal tasks for guest accounts expiring in 1 month'
)]
class CreateGuestUserRenewalTasksCommand extends Command
{
    public function __construct(
        private readonly GuestUserRepository $guestUserRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating Guest Account Renewal Tasks');

        $today = new \DateTime();

        $guestUsers = $this->guestUserRepository->findExpiredInOneMonth();

        if (empty($guestUsers)) {
            $io->info('No guest accounts found expiring soon.');

            return Command::SUCCESS;
        }

        $azguModule = $this->entityManager->getRepository(Module::class)->findOneBy(['name' => 'AZGU']);
        if (!$azguModule) {
            $io->error('MIS module not found');

            return Command::FAILURE;
        }

        /** @var GuestUser $guestUser */
        foreach ($guestUsers as $guestUser) {
            $existingTask = $this->entityManager->getRepository(RenewGuestUser::class)
                ->findOneBy([
                    'guestUser' => $guestUser,
                    'status' => BaseTask::PENDING,
                ]);

            if ($existingTask) {
                $io->note(\sprintf(
                    'Task already exists for guest user %s (%d)',
                    $guestUser->__toString(),
                    $guestUser->getId()
                ));
                continue;
            }

            $task = new RenewGuestUser();
            $task->guestUser = $guestUser;
            $task->setStatus(BaseTask::PENDING);
            $task->shortDescription = 'Guest Account Renewal: '.$guestUser->__toString();
            $task->description = $this->generateTaskDescription($guestUser);
            $task->startedAt = $today;
            $task->module = $azguModule;
            $task->dueDate = (clone $today)->add(new \DateInterval('P1M'));
            $task->escalationTrigger = 30;
            $task->referenceId = $guestUser->getId();

            $representative = $guestUser->getBusinessUnit()?->getRepresentative();

            if (!$representative) {
                $io->error(\sprintf('Representative not found for business unit %s', $guestUser->getBusinessUnit()?->getName()));
                continue;
            }

            $task->assignee = $representative;

            $this->entityManager->persist($task);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function generateTaskDescription(GuestUser $guestUser): string
    {
        $disableDate = $guestUser->getPlannedDisableAt()?->format('Y-m-d');

        return \sprintf(
            "Dear COO or representative,\r\n\r\n".
            "Guest account for %s is going to expire on %s.\r\n\r\n".
            "Do you want to renew it?\r\n\r\n".
            "- Select YES to renew (1-12 months maximum)\r\n".
            "- Select NO to let it expire\r\n\r\n".
            'Without answer to this task, the account will be automatically disabled on %s.',
            $guestUser->__toString(),
            $disableDate,
            $disableDate
        );
    }
}
