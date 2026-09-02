<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\Incoterm;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:esr:incoterm')]
class ImportIncotermCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Connection $legacyConnection
    ) {
        parent::__construct();
        $this->setDescription('Imports Incoterm from legacy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT id, list_key, list_item
            FROM lists
            WHERE lists.list_name = 'list.inco.terms'
            SQL;

        $incotermsLegacy = $this->legacyConnection->executeQuery($sql);

        foreach ($incotermsLegacy->fetchAllAssociative() as $incotermLegacy) {
            $incoterm = new Incoterm();
            $incoterm->code = $incotermLegacy['list_key'];
            $incoterm->description = $incotermLegacy['list_item'];
            $this->em->persist($incoterm);
        }

        $this->em->flush();

        return Command::SUCCESS;
    }
}
