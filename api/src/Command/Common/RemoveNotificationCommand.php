<?php

declare(strict_types=1);

namespace App\Command\Common;

use App\Entity\Common\Notification\Notification;
use App\Repository\Common\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:notification:remove', description: 'Remove all notifications older than a month')]
class RemoveNotificationCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var NotificationRepository $repository */
        $repository = $this->entityManager->getRepository(Notification::class);

        $i = 0;
        foreach ($repository->findOlderThanAMonth() as $notification) {
            $this->entityManager->remove($notification);
            ++$i;

            if ($i > 1000) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
