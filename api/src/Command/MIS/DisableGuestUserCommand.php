<?php

declare(strict_types=1);

namespace App\Command\MIS;

use App\Entity\MIS\GuestUser\GuestUser;
use App\Notifier\MIS\GuestUser\GuestUserNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:guest:disable')]
class DisableGuestUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GuestUserNotifier $guestUserNotifier,
    ) {
        parent::__construct();
        $this->setDescription('Disable Guest users whose departure date has passed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $guestUserRepository = $this->entityManager->getRepository(GuestUser::class);

        /** @var GuestUser $guest */
        foreach ($guestUserRepository->findUsersToDisable() as $guest) {
            $guestUserRepository->disableAndHideGuest($guest);
            $this->guestUserNotifier->sendDisabledNotification($guest);
            $output->writeln(\sprintf('%s, %s disabled', $guest->getLastname(), $guest->getFirstname()));
        }

        return Command::SUCCESS;
    }
}
