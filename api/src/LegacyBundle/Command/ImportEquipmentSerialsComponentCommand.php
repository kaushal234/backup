<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Support\Component;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:equipment:serials:components')]
class ImportEquipmentSerialsComponentCommand extends Command
{
    private const SCHEMATICS_DESCRIPTION = [
        'SCHEM, BRAKING' => 'BSC',
        'SCHEM, ELEC' => 'ESC',
        'SCHEM, HYD' => 'HSC',
        'SCHEM, PNEU' => 'PSC',
        'DIAG, FLOW' => 'FLD',
        'DIAG, GEN ASSEMBLY' => 'GAD',
        'DIAG, PIPING' => 'PPD',
        'DIAG, ROUTING' => 'RTD',
        'PROGRAM' => 'PRG',
        'PARAMETER' => 'PRM',
    ];

    private readonly EntityManagerInterface $em;
    private readonly Connection $legacyConnection;

    public function __construct(
        EntityManagerInterface $em,
        Connection $legacyConnection
    ) {
        parent::__construct();
        $this->setDescription('Import equipment serials components from legacy');
        $this->em = $em;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
            SELECT distinct component
            FROM service_serials
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        if ($stmt->rowCount() > 0) {
            $progress = new ProgressBar($output);
            $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:10s% %memory:6s%');
            $progress->start($stmt->rowCount());

            $operationCounter = 0;
            while ($data = $stmt->fetchAssociative()) {
                if ('' !== $data['component']) {
                    $component = new Component();
                    $component->name = $data['component'];
                    $component->signalCode = self::SCHEMATICS_DESCRIPTION[$component->name] ?? null;
                    $this->em->persist($component);
                    ++$operationCounter;
                }
                $progress->advance();
            }
            $this->em->flush();
            $progress->finish();

            $output->writeln('');
            $output->writeln(\sprintf('<info>Imported <comment>%d</comment> Components </info>', $operationCounter));
        }

        return Command::SUCCESS;
    }
}
