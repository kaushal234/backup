<?php

declare(strict_types=1);

namespace App\Command\Sales\MarketIntelligence;

use App\Entity\Directory\Division;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:mim:division_import', description: 'Import missing division in MIM')]
class MarketIntelligencePopulateDivisionCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mimRepository = $this->entityManager->getRepository(MarketIntelligence::class);
        $divisionRepository = $this->entityManager->getRepository(Division::class);
        $division = $divisionRepository->findOneBy(['name' => 'ALVEST']);

        foreach ($mimRepository->findAll() as $marketIntelligence) {
            if ($marketIntelligence->getDivisions()->contains($division)) {
                continue;
            }
            $marketIntelligence->addDivision($division);
            $this->entityManager->persist($marketIntelligence);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
