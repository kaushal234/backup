<?php

/** @noinspection SqlResolve */

declare(strict_types=1);

namespace App\Command\Support;

use App\Entity\Support\EquipmentSerial;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(name: 'cleanup:serials:duplicated')]
class EquipmentRecordSerialsDuplicatedCleanupCommand extends Command
{
    private readonly Connection $connection;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(Connection $connection, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Cleanup of legacy serials');
        $this->connection = $connection;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('delete_duplicated_serials');
        $logger = new ConsoleLogger($output);
        $totalSerialDeleted = 0;

        $logger->info('Get duplicated schematics');

        $duplicatedSerials = $this->connection->executeQuery(<<<'SQL'
            SELECT equipment_record_id, component_id, model, brand, serial, count(*) AS count
            FROM equipment_serials
            JOIN equipment_serial_components ON equipment_serials.component_id = equipment_serial_components.id
            GROUP BY equipment_record_id, component_id, model, brand, serial
            HAVING count > 1;
            SQL)->fetchAllAssociative();

        $logger->info(\sprintf('%d serials are duplicated', \count($duplicatedSerials)));

        foreach ($duplicatedSerials as $duplicatedSerial) {
            $logger->debug('---');
            $logger->debug(
                \sprintf(
                    '%d occurences for serial : ER id : %d / component id : %d / model : %s / brand : %s / serial : %s',
                    $duplicatedSerial['count'],
                    $duplicatedSerial['equipment_record_id'],
                    $duplicatedSerial['component_id'],
                    $duplicatedSerial['model'] ?? 'null',
                    $duplicatedSerial['brand'] ?? 'null',
                    $duplicatedSerial['serial'] ?? 'null',
                )
            );

            $lineDuplicatedSerials = $this->entityManager->getRepository(EquipmentSerial::class)->findBy([
                'equipmentRecord' => $duplicatedSerial['equipment_record_id'],
                'component' => $duplicatedSerial['component_id'],
                'model' => $duplicatedSerial['model'],
                'brand' => $duplicatedSerial['brand'],
                'serial' => $duplicatedSerial['serial'],
            ]);

            $lineDeleted = 0;
            $serialDeleted = [];

            foreach ($lineDuplicatedSerials as $key => $serial) {
                if ($key === array_key_last($lineDuplicatedSerials)) {
                    continue;
                }

                $this->entityManager->remove($serial);

                $serialDeleted[] = $serial->getId();
                ++$lineDeleted;
                ++$totalSerialDeleted;
            }

            if (\count($lineDuplicatedSerials) > (1 + $lineDeleted)) {
                $logger->error(\sprintf('To few rows must been deleted. %d needed, %d deleted', (int) $duplicatedSerial['count'] - 1, $lineDeleted));

                foreach ($lineDuplicatedSerials as $serial) {
                    $this->entityManager->persist($serial);
                    --$totalSerialDeleted;
                }
                continue;
            }
            if (\count($lineDuplicatedSerials) <= $lineDeleted) {
                $logger->error(\sprintf('To many rows must been deleted. %d needed, %d deleted', (int) $duplicatedSerial['count'] - 1, $lineDeleted));

                foreach ($lineDuplicatedSerials as $serial) {
                    $this->entityManager->persist($serial);
                    --$totalSerialDeleted;
                }
                continue;
            }

            $logger->debug(\sprintf('This serial IDs will be deleted : %s', implode(', ', $serialDeleted)));

            try {
                $logger->debug('---');
                $logger->debug('flush');
                $this->entityManager->flush();
            } catch (\Exception $exception) {
                $logger->error($exception->getMessage());
            }
        }

        $stopwatch->stop('delete_duplicated_serials');

        $logger->debug('---');
        $logger->info(\sprintf('%d serials has been deleted in %s s', $totalSerialDeleted, $stopwatch->getEvent('delete_duplicated_serials')->getDuration() / 1000));

        return Command::SUCCESS;
    }
}
