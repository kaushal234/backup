<?php

declare(strict_types=1);

namespace App\Command\Sales\MarketIntelligence;

use App\Entity\Directory\PositionLevel;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:mim:migration', description: 'Migrate MIM confidential value to position levels')]
class MarketIntelligenceConfidentialMigrationCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mimRepository = $this->entityManager->getRepository(MarketIntelligence::class);
        $positionLevelRepository = $this->entityManager->getRepository(PositionLevel::class);
        $positionLevel = $positionLevelRepository->findOneBy(['label' => PositionLevel::ALVEST_STEERING_COMMITTEE]);

        foreach ($mimRepository->findAll() as $marketIntelligence) {
            if (!$marketIntelligence->isConfidential()) {
                continue;
            }

            $marketIntelligence->addPositionLevel($positionLevel);
            $this->entityManager->persist($marketIntelligence);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
