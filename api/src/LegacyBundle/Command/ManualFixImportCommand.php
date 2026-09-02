<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:equipment:manuals:fix')]
class ManualFixImportCommand extends Command
{
    private readonly Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->setDescription('Fix the wrong associations done during the import of manuals using the DBAL query builder to perform inserts');
        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'dry run the cleanup, the actions reported are not performed')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $manuals = $this->connection->executeQuery('SELECT * FROM manuals ORDER BY legacy_id DESC')->fetchAllAssociative();

        $numberBadEquipmentError = 0;

        foreach ($manuals as $manual) {
            $serials = $this->connection->executeQuery('SELECT * FROM equipment_serials WHERE serial = :legacyId AND component_id = 44 ORDER by id DESC', [
                'legacyId' => (string) $manual['legacy_id'],
            ])->fetchAllAssociative();

            $error = false;

            if (\count($serials) > 1) {
                $logger->emergency(\sprintf('Manual #%s : there are more than one equipment_serial with component MANUAL for this manual legacy id : %s (%d founds)', $manual['id'], $manual['legacy_id'], \count($serials)));
                continue;
            }

            if (\count($serials) < 1) {
                continue;
            }

            $serial = $serials[0];

            if ($manual['equipment_record_id'] !== $serial['equipment_record_id']) {
                $logger->warning(\sprintf('Manual #%s / Serial #%s: not the same ER. Manual ER %s - Serial ER %s', $manual['id'], $serial['id'], $manual['equipment_record_id'], $serial['equipment_record_id']));
                $error = true;
            }

            if ($manual['equipment_serial_id'] !== $serial['id']) {
                $logger->warning(\sprintf('Manual #%s / Serial #%s : not the same SERIAL. Manual SERIAL %s - Serial SERIAL %s', $manual['id'], $serial['id'], $manual['equipment_serial_id'], $serial['id']));
                $error = true;
            }

            if ($error) {
                ++$numberBadEquipmentError;
            }

            if ($error) {
                $logger->info(
                    \sprintf(
                        'Manual #%s : Replace ER  #%s by #%s and serial #%s by %s',
                        $manual['id'],
                        $manual['equipment_record_id'],
                        $serial['equipment_record_id'],
                        $manual['equipment_serial_id'],
                        $serial['id']
                    )
                );

                if (!$input->getOption('dry-run')) {
                    $this->connection->executeQuery(
                        'UPDATE manuals SET equipment_record_id = :equipmentRecord, equipment_serial_id = :equipmentSerial WHERE id = :manualId',
                        [
                            'equipmentRecord' => $serial['equipment_record_id'],
                            'equipmentSerial' => $serial['id'],
                            'manualId' => $manual['id'],
                        ]
                    );
                }
            }
        }

        $logger->emergency(\sprintf('They are a total of %d manual not correctly imported', $numberBadEquipmentError));

        return Command::SUCCESS;
    }
}
