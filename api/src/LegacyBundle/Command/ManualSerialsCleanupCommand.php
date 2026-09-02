<?php

/** @noinspection SqlResolve */

declare(strict_types=1);

namespace LegacyBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:cleanup:serials')]
class ManualSerialsCleanupCommand extends Command
{
    private readonly Connection $legacyConnection;
    private ConsoleLogger $logger;

    public function __construct(Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Cleanup of legacy serials');
        $this->legacyConnection = $legacyConnection;
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'dry run the cleanup, the actions reported are not performed')
            ->addOption('serial', 's', InputOption::VALUE_OPTIONAL, 'restrict the search of duplicates to this serial')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger = new ConsoleLogger($output);

        $this->logger->info('Trim all serials');
        $this->legacyConnection->executeQuery("UPDATE service_serials ss SET ss.serial = TRIM(ss.serial) WHERE component='MANUAL'");

        $equipmentRecordsWithoutManualBefore = $this->getEquipmentRecordsWithNoManual();

        $deletedManualOrphanSerials = $this->deleteManualOrphanSerials($input->getOption('dry-run'));
        $deletedEquipmentRecordOrphanSerials = $this->deleteEquipmentRecordOrphanSerials($input->getOption('dry-run'));
        $deletedManualDocs = $this->deleteOrphanManualDocs($input->getOption('dry-run'));
        $deletedManualDiags = $this->deleteOrphanManualDiags($input->getOption('dry-run'));
        $deletedManualParts = $this->deleteOrphanManualParts($input->getOption('dry-run'));
        $deletedManualDownloads = $this->deleteOrphanManualDownloads($input->getOption('dry-run'));

        $equipmentRecordsWithoutManualAfterOrphanRemoval = $this->getEquipmentRecordsWithNoManual();

        $serialCondition = null !== ($serialFilter = $input->getOption('serial')) ? "AND ss.serial='$serialFilter'" : '';
        $serialDuplicates = $this->legacyConnection->executeQuery(<<<SQL
            SELECT ss.serial, COUNT(*) AS count
            FROM service_serials ss
            WHERE ss.component = 'MANUAL' $serialCondition
            GROUP by ss.serial
            HAVING count > 1
            ORDER BY ss.serial DESC
            SQL)->fetchAllAssociative();

        $manualsIndex = [];
        foreach ($serialDuplicates as $serialDuplicate) {
            $manualsIndex[$serialDuplicate['serial']] = $serialDuplicate['count'];
        }

        $allSerials = $this->countAllSerialRows($serialCondition);

        $allUniqueSerials = $this->countUniqueSerialRows($serialCondition);

        $serialsToDelete = $manualsToDuplicate = [];

        foreach (array_column($serialDuplicates, 'serial') as $serial) {
            $this->logger->debug("Processing duplicated serial entries for serial '$serial'");

            $rows = $this->legacyConnection->executeQuery(<<<'SQL'
                SELECT ss.id, ss.parent_id as equipmentRecord
                FROM service_serials ss
                WHERE ss.component = 'MANUAL' AND ss.serial = :serial
                SQL, ['serial' => $serial])->fetchAllAssociative();

            foreach ($rows as $index => $row) {
                $equipmentRecordOtherManuals = $this->legacyConnection->executeQuery(<<<'SQL'
                    SELECT ss.id, ss.parent_id as equipmentRecord, ss.serial
                    FROM service_serials ss
                    WHERE ss.component = 'MANUAL'
                    AND ss.parent_id = :equipmentRecord
                    AND ss.id != :id
                    SQL, ['equipmentRecord' => $row['equipmentRecord'], 'id' => $row['id']])->fetchAllAssociative();

                if (0 !== $index) {
                    $this->logger->debug("{$this->formatPrefix($serial, $row['id'])} No other serials for ER {$row['equipmentRecord']}, the manual $serial must be duplicated and the serial entry {$row['id']} must be updated with the new manual id");

                    $manualsToDuplicate[$row['id']] = $serial;
                    continue;
                }

                foreach ($equipmentRecordOtherManuals as $equipmentRecordOtherManual) {
                    $plannedDeletionCountByManualId = array_count_values($serialsToDelete);
                    $remainingSerialsForManual = $manualsIndex[$serial] - ($plannedDeletionCountByManualId[$serial] ?? 0);
                    switch (true) {
                        case $equipmentRecordOtherManual['serial'] === $serial:
                            if (isset($serialsToDelete[$row['id']]) || 1 === $remainingSerialsForManual) {
                                break;
                            }
                            $this->logger->debug("{$this->formatPrefix($serial, $row['id'])} The serial entry {$equipmentRecordOtherManual['id']} must be deleted, it has the same manual for the same ER");
                            $serialsToDelete[$equipmentRecordOtherManual['id']] = $serial;
                            break;
                        case $equipmentRecordOtherManual['serial'] > $serial:
                            if (isset($serialsToDelete[$equipmentRecordOtherManual['id']]) || 1 === $remainingSerialsForManual) {
                                break;
                            }
                            $this->logger->debug("{$this->formatPrefix($serial, $row['id'])} The serial entry {$row['id']} can safely be deleted, the row {$equipmentRecordOtherManual['id']} refers to a more recent manual ({$equipmentRecordOtherManual['serial']})");
                            $serialsToDelete[$row['id']] = $serial;
                            break;
                    }
                }
            }
        }

        if ($input->getOption('dry-run')) {
            usort($serialDuplicates, static function ($a, $b) {
                if ($a['count'] === $b['count']) {
                    return 0;
                }

                return ($a['count'] < $b['count']) ? -1 : 1;
            });

            $table = new Table($output);
            $table
                ->setHeaderTitle('Duplicates report')
                ->setHeaders(['Serial', 'Count'])
                ->setRows($serialDuplicates);
            $table->render();
        }

        $table = new Table($output);
        $table
            ->setHeaderTitle('Actions report')
            ->setRows([
                ['ER with no manual before', \count($equipmentRecordsWithoutManualBefore)],
                ['Deleted manual orphan serials', $deletedManualOrphanSerials],
                ['Deleted ER orphan serials', $deletedEquipmentRecordOrphanSerials],
                ['ER with no manual after orphan removal', \count($equipmentRecordsWithoutManualAfterOrphanRemoval)],
                ['Total Serials rows before', $allSerials],
                ['Unique Serials rows before', $allUniqueSerials],
                ['Number of duplicated rows before', array_sum(array_column($serialDuplicates, 'count'))],
                ['Number of unique duplicated rows before', \count($serialDuplicates)],
                ['Manuals to delete', $deleted = \count($serialsToDelete)],
                ['Manuals to duplicate', \count($manualsToDuplicate)],
                ['Total rows expected after', $allSerials - $deleted],
                ['Deleted orphan manuals_docs', $deletedManualDocs],
                ['Deleted orphan manuals_diag', $deletedManualDiags],
                ['Deleted orphan manuals_parts', $deletedManualParts],
                ['Deleted orphan manuals_downloads', $deletedManualDownloads],
            ])
        ;
        $table->render();

        if (!$input->getOption('dry-run')) {
            foreach ($serialsToDelete as $serialId => $manualId) {
                $this->deleteSerial((int) $serialId, (int) $manualId);
            }

            $manualDocuments = [];
            $documentsCache = [];
            $this->logger->debug('Preparing duplication of manuals');
            foreach ($manualsToDuplicate as $serialId => $manualId) {
                if (null !== ($documentsCache[$manualId] ?? null)) {
                    $manualDocuments[$serialId] = $documentsCache[$manualId];
                    continue;
                }
                $manualDocuments[$serialId] = $this->legacyConnection->executeQuery(<<<'SQL'
                    SELECT id, item, doc_num
                    FROM manuals_docs
                    WHERE parent_id = :manualId
                    SQL, ['manualId' => $manualId])->fetchAllAssociative();
                $documentsCache[$manualId] = $manualDocuments[$serialId];
            }

            $i = 0;
            $this->legacyConnection->beginTransaction();
            foreach ($manualsToDuplicate as $serialId => $manualId) {
                $this->logger->debug("Duplicating manual entry '$manualId' for serial ID '$serialId'");
                $this->duplicateSerial((int) $serialId, (int) $manualId, $manualDocuments[$serialId]);

                ++$i;
                if (0 === $i % 50) {
                    $this->legacyConnection->commit();
                    $this->legacyConnection->beginTransaction();
                }
            }
            $this->legacyConnection->commit();
        }

        $table = new Table($output);
        $table
            ->setHeaderTitle('Actions report')
            ->setRows([
                ['ER with no manual after clean up', \count($equipmentRecordsWithoutManualAfter = $this->getEquipmentRecordsWithNoManual())],
                ['Total Serials rows after', $this->countAllSerialRows($serialCondition)],
                ['Unique Serials rows after', $this->countUniqueSerialRows($serialCondition)],
            ])
        ;
        $table->render();

        $table = new Table($output);
        $table
            ->setHeaderTitle('New Equipment Records Orphans')
            ->setRows(array_map(static fn ($er) => [$er], array_diff($equipmentRecordsWithoutManualAfter, $equipmentRecordsWithoutManualAfterOrphanRemoval)))
        ;
        $table->render();

        return Command::SUCCESS;
    }

    private function deleteManualOrphanSerials(bool $dryRun): int
    {
        $deletedManualOrphanSerials = (int) $this->legacyConnection->executeQuery(<<<'SQL'
                SELECT COUNT(*) AS count FROM service_serials
                WHERE id IN (
                    SELECT ss.id
                    FROM service_serials ss
                    LEFT JOIN manuals m on ss.serial = m.id
                    WHERE component='MANUAL'
                    AND m.id IS NULL
                );
            SQL)->fetchOne();
        $this->logger->info('Remove {deletedManualOrphanSerials} serials referring to a non-existing manual', ['deletedManualOrphanSerials' => $deletedManualOrphanSerials]);

        if ($dryRun) {
            return $deletedManualOrphanSerials;
        }

        $this->legacyConnection->executeQuery(<<<'SQL'
                DELETE FROM service_serials
                WHERE id IN (
                    SELECT ss.id
                    FROM service_serials ss
                    LEFT JOIN manuals m on ss.serial = m.id
                    WHERE component='MANUAL'
                    AND m.id IS NULL
                );
            SQL);

        return $deletedManualOrphanSerials;
    }

    private function deleteEquipmentRecordOrphanSerials(bool $dryRun): int
    {
        $deletedEquipmentRecordOrphanSerials = (int) $this->legacyConnection->executeQuery(<<<'SQL'
                SELECT COUNT(*) AS count FROM service_serials
                WHERE id IN (
                    SELECT ss.id
                    FROM service_serials ss
                    LEFT JOIN service er on ss.parent_id = er.id
                    WHERE component='MANUAL'
                    AND er.id IS NULL
                );
            SQL)->fetchOne();
        $this->logger->info('Remove {deletedEquipmentRecordOrphanSerials} serials referring to a non-existing ER', ['deletedEquipmentRecordOrphanSerials' => $deletedEquipmentRecordOrphanSerials]);

        if ($dryRun) {
            return $deletedEquipmentRecordOrphanSerials;
        }

        $this->legacyConnection->executeQuery(<<<'SQL'
                DELETE FROM service_serials
                WHERE id IN (
                    SELECT ss.id
                    FROM service_serials ss
                    LEFT JOIN service er on ss.parent_id = er.id
                    WHERE component='MANUAL'
                    AND er.id IS NULL
                );
            SQL);

        return $deletedEquipmentRecordOrphanSerials;
    }

    private function deleteOrphanManualDocs(bool $dryRun): int
    {
        $deletedOrphanManualDocs = (int) $this->legacyConnection->executeQuery(<<<'SQL'
                SELECT COUNT(*) AS count FROM manuals_docs
                WHERE id IN (
                    SELECT md.id
                    FROM manuals_docs md
                        LEFT JOIN manuals m on md.parent_id = m.id
                    WHERE m.id IS NULL
                );
            SQL)->fetchOne();
        $this->logger->info('Remove {deletedOrphanManualDocs} manual_doc referring to a non-existing manual', ['deletedOrphanManualDocs' => $deletedOrphanManualDocs]);

        if ($dryRun) {
            return $deletedOrphanManualDocs;
        }

        $this->legacyConnection->executeQuery(<<<'SQL'
                DELETE FROM manuals_docs
                WHERE id IN (
                    SELECT md.id
                    FROM manuals_docs md
                        LEFT JOIN manuals m on md.parent_id = m.id
                    WHERE m.id IS NULL
                );
            SQL);

        return $deletedOrphanManualDocs;
    }

    private function deleteOrphanManualDiags(bool $dryRun): int
    {
        $deletedOrphanManualDiags = (int) $this->legacyConnection->executeQuery(<<<'SQL'
                SELECT COUNT(*) AS count FROM manuals_diag
                WHERE id IN (
                    SELECT diag.id
                    FROM manuals_diag diag
                        LEFT JOIN manuals_docs docs on diag.id = docs.doc_num
                    WHERE docs.id IS NULL
                );
            SQL)->fetchOne();
        $this->logger->info('Remove {deletedOrphanManualDiags} manuals_diag referring to a non-existing manuals_doc', ['deletedOrphanManualDiags' => $deletedOrphanManualDiags]);

        if ($dryRun) {
            return $deletedOrphanManualDiags;
        }

        $this->legacyConnection->executeQuery(<<<'SQL'
                DELETE FROM manuals_diag
                WHERE id IN (
                    SELECT diag.id
                    FROM manuals_diag diag
                        LEFT JOIN manuals_docs docs on diag.id = docs.doc_num
                    WHERE docs.id IS NULL
                );
            SQL);

        return $deletedOrphanManualDiags;
    }

    private function deleteOrphanManualParts(bool $dryRun): int
    {
        $deletedOrphanManualParts = (int) $this->legacyConnection->executeQuery(<<<'SQL'
                SELECT COUNT(*) AS count FROM manuals_parts
                WHERE id IN (
                    SELECT mp.id
                    FROM manuals_parts mp
                        LEFT JOIN manuals_diag md on mp.parent_id = md.id
                    WHERE md.id IS NULL
                );
            SQL)->fetchOne();
        $this->logger->info('Remove {deletedOrphanManualParts} manuals_parts referring to a non-existing manuals_diag', ['deletedOrphanManualParts' => $deletedOrphanManualParts]);

        if ($dryRun) {
            return $deletedOrphanManualParts;
        }

        $this->legacyConnection->executeQuery(<<<'SQL'
                DELETE FROM manuals_parts
                WHERE id IN (
                    SELECT mp.id
                    FROM manuals_parts mp
                        LEFT JOIN manuals_diag md on mp.parent_id = md.id
                    WHERE md.id IS NULL
                );
            SQL);

        return $deletedOrphanManualParts;
    }

    private function deleteOrphanManualDownloads(bool $dryRun): int
    {
        $deletedOrphanManualDownloads = (int) $this->legacyConnection->executeQuery(<<<'SQL'
                SELECT COUNT(*) AS count FROM manuals_downloads
                WHERE id IN (
                    SELECT md.id
                    FROM manuals_downloads md
                        LEFT JOIN tld.manuals ma on md.parent_id = ma.id
                    WHERE ma.id IS NULL
                );
            SQL)->fetchOne();
        $this->logger->info('Remove {deletedOrphanManualDownloads} manuals_downloads referring to a non-existing manual', ['deletedOrphanManualDownloads' => $deletedOrphanManualDownloads]);

        if ($dryRun) {
            return $deletedOrphanManualDownloads;
        }

        $this->legacyConnection->executeQuery(<<<'SQL'
                DELETE FROM manuals_downloads
                WHERE id IN (
                    SELECT md.id
                    FROM manuals_downloads md
                        LEFT JOIN tld.manuals ma on md.parent_id = ma.id
                    WHERE ma.id IS NULL
                );
            SQL);

        return $deletedOrphanManualDownloads;
    }

    private function formatPrefix($serial, $id): string
    {
        return \sprintf('[SERIAL %s - ROW %s]', $serial, $id);
    }

    private function getEquipmentRecordsWithNoManual(): array
    {
        return array_column($this->legacyConnection->executeQuery(<<<'SQL'
                SELECT er.sn
                FROM service er
                LEFT JOIN service_serials ss on ss.parent_id = er.id AND ss.component = 'MANUAL'
                WHERE ss.id IS NULL
                ORDER BY er.id DESC
            SQL)->fetchAllAssociative(), 'sn');
    }

    private function deleteSerial(int $serialId, int $manualId): void
    {
        $this->logger->debug("Deleting serial entry '$serialId' and its dependencies");

        $this->logger->debug("Deleting duplicated serial entry '$serialId'");
        $this->legacyConnection->executeQuery(<<<'SQL'
                DELETE FROM service_serials WHERE id = :serialId;
            SQL, ['serialId' => $serialId]);
    }

    private function duplicateSerial(int $serialId, int $manualId, array $documents): void
    {
        $this->legacyConnection->executeQuery(<<<'SQL'
                INSERT INTO manuals (erp, brand, model, date, description, features, lang, status)
                SELECT erp, brand, model, date, description, features, lang, status
                FROM manuals
                WHERE id = :manualId;
            SQL, ['manualId' => $manualId]);
        $newManualId = (int) $this->legacyConnection->lastInsertId();

        $this->logger->debug("Updating serial entry '$serialId' with newly created manual '$newManualId'");
        $this->legacyConnection->executeQuery(<<<'SQL'
                UPDATE service_serials
                SET serial = :newManualId
                WHERE id = :serialId;
            SQL, ['newManualId' => $newManualId, 'serialId' => $serialId]);

        $this->legacyConnection->executeQuery(<<<'SQL'
            INSERT INTO manuals_downloads (parent_id, dt_entered, poster_id, vendor_id, er_id, erp, std_manual, full_manual, extra_cd, chapter_5, dt_delivery, downloaded, hide, comment)
            SELECT :newManualId, dt_entered, poster_id, vendor_id, er_id, erp, std_manual, full_manual, extra_cd, chapter_5, dt_delivery, downloaded, hide, comment
            FROM manuals_downloads
            WHERE parent_id = :manualId
            SQL, ['manualId' => $manualId, 'newManualId' => $newManualId]);

        $this->logger->debug("Duplicating document entries for the new manual ID '{newManualId}'", ['newManualId' => $newManualId]);
        foreach ($documents as $document) {
            $this->legacyConnection->executeQuery(<<<'SQL'
                INSERT INTO manuals_docs (parent_id, item, doc_num)
                VALUES (:newManualId, :item, :doc_num)
                SQL, ['newManualId' => $newManualId, 'item' => $document['item'], 'doc_num' => $document['doc_num']]);
            $newDocumentId = (int) $this->legacyConnection->lastInsertId();

            $this->logger->debug("Duplicating diag entry for the new document ID '{newDocumentId}'", ['newDocumentId' => $newDocumentId]);
            $this->legacyConnection->executeQuery(<<<'SQL'
                INSERT INTO manuals_diag (parent_id, dt_created, erp, factory_num, rev, category, doc_type, endescription, frdescription, diagram_filename, ennotes, frnotes, hmd5, filesize)
                SELECT parent_id, dt_created, erp, factory_num, rev, category, doc_type, endescription, frdescription, diagram_filename, ennotes, frnotes, hmd5, filesize
                FROM manuals_diag
                WHERE id = :diagId
                SQL, ['diagId' => $document['doc_num']])->fetchAllAssociative();
            $newDiagId = (int) $this->legacyConnection->lastInsertId();

            $this->logger->debug("Duplicating part entries for the new diagram ID '{newDiagId}'", ['newDiagId' => $newDiagId]);
            $this->legacyConnection->executeQuery(<<<'SQL'
                INSERT INTO manuals_parts (parent_id, item, pn, vendor_pn, ocm_pn, qty, um, en, fr, dscu, note, group_p, group_m, group_o, group_c)
                SELECT :newDiagId, item, pn, vendor_pn, ocm_pn, qty, um, en, fr, dscu, note, group_p, group_m, group_o, group_c
                FROM manuals_parts
                WHERE parent_id = :previousDiagId
                SQL, ['newDiagId' => $newDiagId, 'previousDiagId' => $document['doc_num']]);

            $this->logger->debug("Updating document '{newDocumentId}' with the new diag entry ID '{newDiagId}'", ['newDocumentId' => $newDocumentId, 'newDiagId' => $newDiagId]);
            $this->legacyConnection->executeQuery(<<<'SQL'
                UPDATE manuals_docs SET doc_num = :newDiagId
                WHERE id = :newDocumentId
                SQL, ['newDocumentId' => $newDocumentId, 'newDiagId' => $newDiagId]);
        }
    }

    private function countAllSerialRows(string $serialCondition): int
    {
        return (int) $this->legacyConnection->executeQuery(<<<SQL
            SELECT COUNT(*) AS count
            FROM service_serials ss
            WHERE ss.component = 'MANUAL' $serialCondition
            SQL)->fetchOne();
    }

    private function countUniqueSerialRows(string $serialCondition): int
    {
        return (int) $this->legacyConnection->executeQuery(<<<SQL
            SELECT COUNT(DISTINCT ss.serial) AS count
            FROM service_serials ss
            WHERE ss.component = 'MANUAL' $serialCondition
            SQL)->fetchOne();
    }
}
