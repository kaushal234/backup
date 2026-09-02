<?php

declare(strict_types=1);

namespace App\Command\MIS;

use App\Entity\MIS\GuestUser\GuestUser;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\SequenceManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:guest:activate')]
class ActivateGuestUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SequenceManager $sequenceManager,
    ) {
        parent::__construct();
        $this->setDescription('Activate Guest users');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $guestUserRepository = $this->entityManager->getRepository(GuestUser::class);

        /** @var GuestUser $guest */
        foreach ($guestUserRepository->findUsersToActivate() as $guest) {
            if (0 === \count($this->sequenceManager->findApprovedSequencesForGuestUser($guest))) {
                continue;
            }
            $guestUserRepository->enableAndUnhidePeople($guest);
            $output->writeln(\sprintf('%s, %s activated', $guest->getLastname(), $guest->getFirstname()));
        }

        return Command::SUCCESS;
    }
}
