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

#[AsCommand(name: 'tld:mim:fix_confidential', description: 'Fix confidentiality in MIM')]
class MarketIntelligenceFixConfidentialCommand extends Command
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
        $positionLevelExecutive = $positionLevelRepository->findOneBy(['label' => 'EXECUTIVES']);
        $positionLevelSteeringCommittee = $positionLevelRepository->findOneBy(['label' => 'ALVEST STEERING COMMITTEE']);

        foreach ($mimRepository->findAll() as $marketIntelligence) {
            if ($marketIntelligence->getPositionLevels()->isEmpty()) {
                continue;
            }
            if (1 === $marketIntelligence->getPositionLevels()->count()) {
                foreach ($marketIntelligence->getPositionLevels() as $positionLevel) {
                    if ('MANAGERS' === $positionLevel->getLabel()) {
                        $marketIntelligence->removePositionLevel($positionLevel);
                        $marketIntelligence->addPositionLevel($positionLevelExecutive);
                    }
                }
            }

            $marketIntelligence->addPositionLevel($positionLevelSteeringCommittee);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
