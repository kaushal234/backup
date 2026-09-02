<?php

declare(strict_types=1);

namespace App\ION\Command\GoLiveMigrations;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsCommand(name: 'ion:migration')]
class MigrationCommand extends Command
{
    private readonly Connection $legacyConnection;

    private array $config = [
        'equipment_records' => [
            'tables' => ['service'],
            'config' => [
                [
                    'column' => 't_prno',
                    'original_type' => 'VARCHAR',
                    'type' => 'VARCHAR',
                    'original_length' => 20,
                    'length' => 20,
                    'prefix' => 'P',
                ],
                [
                    'column' => 't_pdno',
                    'original_type' => 'VARCHAR',
                    'type' => 'VARCHAR',
                    'original_length' => 20,
                    'length' => 23,
                    'prefix' => 'W',
                ],
            ], ],
        'PIO' => [
            'tables' => ['pi_crab_eap', 'pi_notifications', 'pi_operations_status', 'pi_questions_unit', 'pi_speed_logs'],
            'config' => [
                [
                    'column' => 't_cprj',
                    'original_type' => 'INT',
                    'type' => 'VARCHAR',
                    'original_length' => 6,
                    'length' => 23,
                    'prefix' => 'P',
                ],
                [
                    'column' => 't_pdno',
                    'original_type' => 'INT',
                    'type' => 'VARCHAR',
                    'original_length' => 6,
                    'length' => 23,
                    'prefix' => 'W',
                ],
            ], ],
    ];

    private ConsoleLogger $logger;

    public function __construct(Connection $legacyConnection)
    {
        parent::__construct();
        $this
            ->setDescription('Legacy SQL Request to migrate data for LN')
            ->addArgument('action', InputArgument::REQUIRED, 'Action done by command. Can be "create", "migrate", "rollback"')
            ->addOption(
                'for',
                'for',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Play the migration only for this location'
            )
            ->addOption(
                'exclude',
                'ex',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'Exclude this location of migration'
            )
        ;

        $this->legacyConnection = $legacyConnection;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');
        if (!\in_array($action, ['create', 'migrate', 'rollback'], true)) {
            throw new \LogicException('Unknown action. Can be "create", "migrate", "rollback"');
        }

        $this->logger = new ConsoleLogger($output);

        if ('create' === $action) {
            $this->logger->info('[CREATE NEW COLUMN]');
            $stopwatch = new Stopwatch();
            $stopwatch->start('create_column');
            $this->legacyConnection->beginTransaction();

            foreach ($this->config as $value) {
                foreach ($value['tables'] as $table) {
                    foreach ($value['config'] as $column) {
                        $this->legacyConnection->executeQuery(\sprintf('ALTER TABLE %s CHANGE %s %s %s(%d) NOT NULL;', $table, $column['column'], $column['column'].'_baan', $column['original_type'], $column['original_length']));
                        $this->legacyConnection->executeQuery(\sprintf("ALTER TABLE %s ADD %s %s(%d) NOT NULL DEFAULT '';", $table, $column['column'], $column['type'], $column['length']));
                        $this->legacyConnection->executeQuery(\sprintf('UPDATE %s SET %s = %s', $table, $column['column'], $column['column'].'_baan'));
                    }
                }
            }

            try {
                $this->legacyConnection->commit();
                $event = $stopwatch->stop('create_column');
                $this->logger->info(\sprintf('[CREATE NEW COLUMN] New column are created on %f s', $event->getDuration() / 1000));
            } catch (\Exception $exception) {
                $stopwatch->stop('create_column');
                $this->legacyConnection->rollBack();
                throw new \InvalidArgumentException(\sprintf('something went wrong when trying to create new column : %s', $exception->getMessage()), $exception->getCode(), $exception);
            }

            return Command::SUCCESS;
        }

        $forLocations = $input->getOption('for');
        $excludeLocations = $input->getOption('exclude');
        if ([] !== $forLocations && [] !== $excludeLocations) {
            throw new \LogicException('Impossible to have the "FOR" and "EXCLUDE" options on the same time');
        }

        $conditionType = null;
        $locationIds = array_merge($forLocations, $excludeLocations);
        $locationsName = [];

        if ([] !== $forLocations) {
            $conditionType = 'for';
        } elseif ([] !== $excludeLocations) {
            $conditionType = 'exclude';
        }

        if ([] !== $locationIds) {
            $locationsName = $this->legacyConnection->executeQuery(\sprintf('SELECT location FROM locations WHERE erp in (%s)', implode(', ', $locationIds)))->fetchFirstColumn();

            if (\count($locationIds) !== \count($locationsName)) {
                throw new \Exception(\sprintf("We don't find the same number of BU on the database. Given : %s / Database : %s", implode(', ', $locationIds), implode(', ', $locationsName)));
            }
        }

        if ('migrate' === $action) {
            $this->logger->info('[MIGRATE DATA]');
            $stopwatch = new Stopwatch();
            $stopwatch->start('migrate_data');
            $this->legacyConnection->beginTransaction();
            $isForPowervamp = 'for' === $conditionType && 1 === \count($locationIds) && '220' === $locationIds[0];

            try {
                foreach ($this->config as $type => $value) {
                    foreach ($value['tables'] as $table) {
                        foreach ($value['config'] as $column) {
                            $condition = $this->createLocationCondition($conditionType, $type, $locationIds, $locationsName);

                            if (!$isForPowervamp && $this->legacyConnection->executeQuery(\sprintf("SELECT count(*) from %s WHERE %s REGEXP '^%s[2-9][0-9]' %s", $table, $column['column'], $column['prefix'], $condition))->fetchOne()) {
                                throw new \Exception(\sprintf('Column %s of table %s already updated with LN value', $column['column'], $table));
                            }

                            $conditionUpdate = $this->createLocationCondition($conditionType, $type, $locationIds, $locationsName, 'WHERE');
                            $this->legacyConnection->executeQuery(\sprintf('UPDATE %s SET %s = %s %s', $table, $column['column'].'_baan', $column['column'], $conditionUpdate));

                            if ($isForPowervamp) {
                                if ('t_prno' === $column['column']) {
                                    $condition .= " AND t_prno NOT LIKE 'T%'";
                                }

                                if ('t_cprj' === $column['column']) {
                                    $condition .= " AND t_cprj NOT LIKE 'T%'";
                                }

                                if ('t_pdno' === $column['column']) {
                                    $condition .= " AND t_pdno NOT LIKE 'W22%'";
                                }

                                if ('service' === $table) {
                                    $condition .= " AND date_entered < '2022-07-01'";
                                }
                            }

                            if ('equipment_records' === $type) {
                                $this->legacyConnection->executeQuery(\sprintf("UPDATE service SET %1\$s = IFNULL(CONCAT('%2\$s', SUBSTR((SELECT erp FROM locations WHERE service.man_location = locations.location), 1, 2), %1\$s_baan), %1\$s_baan) WHERE %1\$s_baan != '' %3\$s;", $column['column'], $column['prefix'], $condition));
                            }

                            if ('PIO' === $type) {
                                $this->legacyConnection->executeQuery(\sprintf("UPDATE %1\$s SET %2\$s = CONCAT('%3\$s', SUBSTR(comp, 1, 2), %2\$s_baan) WHERE %2\$s_baan != 0 %4\$s;", $table, $column['column'], $column['prefix'], $condition));
                            }
                        }
                    }
                }

                $this->legacyConnection->commit();
                $event = $stopwatch->stop('migrate_data');
                $this->logger->info(\sprintf('[MIGRATE DATA]] New date for LN created on %f s', $event->getDuration() / 1000));
            } catch (\Exception $exception) {
                $stopwatch->stop('migrate_data');
                $this->legacyConnection->rollBack();
                throw new \InvalidArgumentException(\sprintf('something went wrong when trying to data for LN : %s', $exception->getMessage()), $exception->getCode(), $exception);
            }
        } elseif ('rollback' === $action) {
            $this->logger->info('[MIGRATION ROLLBACK] start');
            $this->legacyConnection->beginTransaction();
            $stopwatch = new Stopwatch();
            $stopwatch->start('migration_rollback');

            foreach ($this->config as $type => $value) {
                foreach ($value['tables'] as $table) {
                    foreach ($value['config'] as $column) {
                        $condition = $this->createLocationCondition($conditionType, $type, $locationIds, $locationsName);
                        if ('equipment_records' === $type) {
                            $this->legacyConnection->executeQuery(\sprintf("UPDATE service SET %1\$s = %1\$s_baan WHERE %1\$s_baan != '' %2\$s;", $column['column'], $condition));
                        }

                        if ('PIO' === $type) {
                            $this->legacyConnection->executeQuery(\sprintf('UPDATE %1$s SET %2$s = %2$s_baan WHERE %2$s_baan != 0 %3$s;', $table, $column['column'], $condition));
                        }
                    }
                }
            }

            try {
                $this->legacyConnection->commit();
                $event = $stopwatch->stop('migration_rollback');
                $this->logger->info(\sprintf('[MIGRATION ROLLBACK] Correctly executed on %f s', $event->getDuration() / 1000));
            } catch (\Exception $exception) {
                $stopwatch->stop('migration_rollback');
                $this->legacyConnection->rollBack();
                throw new \InvalidArgumentException(\sprintf('[MIGRATION ROLLBACK] something went wrong when trying to rollback migration : %s', $exception->getMessage()), $exception->getCode(), $exception);
            }
        }

        return Command::SUCCESS;
    }

    private function createLocationCondition(?string $conditionType, string $type, array $locationsId, array $locationsName, string $operator = 'AND'): string
    {
        if (null === $conditionType) {
            return '';
        }

        if ('equipment_records' === $type) {
            return \sprintf(' %s man_location %s (%s)', $operator, ('for' === $conditionType) ? 'IN' : 'NOT IN', \sprintf("'%s'", implode("', '", $locationsName)));
        }

        if ('PIO' === $type) {
            return \sprintf(' %s comp %s (%s)', $operator, ('for' === $conditionType) ? 'IN' : 'NOT IN', implode(', ', $locationsId));
        }

        return '';
    }
}
