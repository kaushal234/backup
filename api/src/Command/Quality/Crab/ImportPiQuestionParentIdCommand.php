<?php

declare(strict_types=1);

namespace App\Command\Quality\Crab;

use App\Entity\Quality\Crab;
use App\Repository\Quality\Crab\CrabRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\CrabManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:crab:import-pi-question-parent-id')]
class ImportPiQuestionParentIdCommand extends Command
{
    public function __construct(
        private readonly CrabManager $crabManager,
        private readonly CrabRepository $crabRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $crabs = $this->crabRepository->findCrabsWithoutPiQuestionParentId();

        $output->writeln(\sprintf('<info>%d crabs found</info>', \count($crabs)));

        $count = 0;
        $countNotFound = 0;

        foreach ($crabs as $crab) {
            /** @var Crab $crab */
            $parentId = $this->crabManager->getPiQuestionParentId($crab);

            if (null === $parentId) {
                ++$countNotFound;

                $output->writeln(\sprintf(
                    '<comment>No parent_id found for crab #%d (piQuestionId %d)</comment>',
                    $crab->getId(),
                    $crab->piQuestionId
                ));

                continue;
            }

            $crab->piQuestionParentId = $parentId;

            ++$count;

            if (0 === $count % 500) {
                $this->entityManager->flush();

                $output->writeln(\sprintf('<fg=yellow>Flushed %d records</>', $count));
            }
        }

        $this->entityManager->flush();

        $output->writeln(\sprintf('<info>%d not found</info>', $countNotFound));
        $output->writeln(\sprintf('<fg=green;options=bold>%d piQuestionParentId updated</>', $count));

        return Command::SUCCESS;
    }
}
