<?php

declare(strict_types=1);

namespace App\Command\Quality\Crab;

use App\Entity\Quality\Crab;
use App\Entity\Quality\NonConformity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:crab:migration:ncr')]
class CrabMigrationForNonConformityCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $crabRepository = $this->em->getRepository(Crab::class);
        $nonConformityRepository = $this->em->getRepository(NonConformity::class);

        foreach ($nonConformityRepository->findAll() as $ncr) {
            if (null === ($crabId = $ncr->crabId)) {
                continue;
            }
            $ncr->crab = $crabRepository->find($crabId);
        }
        $this->em->flush();

        return Command::SUCCESS;
    }
}
