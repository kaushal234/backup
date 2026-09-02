<?php

/** @noinspection SqlResolve */

declare(strict_types=1);

namespace LegacyBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:cleanup:manuals')]
class ManualCleanupCommand extends Command
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Cleanup of legacy manuals : Delete duplicated docs on same manual');
        $this->legacyConnection = $legacyConnection;
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'dry run the cleanup, the actions reported are not performed')
            ->addOption('manuals', 'm', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'restrict the search of specific manuals')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);

        $logger->notice('Search duplicating manuals_diag for the same manual');

        $deletedDuplicatedDocsOnSameManual = (int) $this->legacyConnection->executeQuery(
            <<<'SQL'
                    SELECT count(*)
                    FROM (
                        SELECT md.doc_num, COUNT(*) AS count
                        FROM manuals_docs md
                        GROUP by md.doc_num, md.parent_id
                        HAVING count > 1
                        ORDER BY md.doc_num DESC
                    ) AS duplicate_diags;
                SQL
        )->fetchOne();
        $logger->notice(
            '{deletedDuplicatedDocsOnSameManual} diag are duplicating',
            ['deletedDuplicatedDocsOnSameManual' => $deletedDuplicatedDocsOnSameManual]
        );

        if (!$input->getOption('dry-run')) {
            $duplicatedDocsOnSameManual = $this->legacyConnection->executeQuery(
                <<<'SQL'
                        SELECT md.parent_id, md.doc_num, COUNT(*) AS count
                        FROM manuals_docs md
                        GROUP by md.doc_num, md.parent_id
                        HAVING count > 1
                        ORDER BY md.doc_num DESC;
                    SQL
            )->fetchAllAssociative();

            foreach ($duplicatedDocsOnSameManual as $duplicatedDoc) {
                $logger->info(
                    'Remove duplicated manuals_docs for manual_diag #{docDiag} on manual #{manualId}',
                    ['docDiag' => $duplicatedDoc['doc_num'], 'manualId' => $duplicatedDoc['parent_id']]
                );

                $idDuplicatedDocs = $this->legacyConnection->executeQuery(
                    <<<'SQL'
                            SELECT md.id
                            FROM manuals_docs md
                            WHERE md.doc_num = :doc_num AND md.parent_id = :manual_id
                        SQL, ['doc_num' => $duplicatedDoc['doc_num'], 'manual_id' => $duplicatedDoc['parent_id']]
                )->fetchAllAssociative();

                $idSavedDoc = array_shift($idDuplicatedDocs);

                $logger->debug(
                    'Save manuals_docs #{docId} for manual_diag #{docNum} on manual #{manualId}',
                    ['docId' => $idSavedDoc['id'], 'docNum' => $duplicatedDoc['doc_num'], 'manualId' => $duplicatedDoc['parent_id']]
                );

                foreach ($idDuplicatedDocs as $doc) {
                    $result = $this->legacyConnection->executeStatement(<<<'SQL'
                            DELETE FROM manuals_docs
                            WHERE id = :doc_id;
                        SQL, ['doc_id' => $doc['id']]);

                    if ($result) {
                        $logger->debug(
                            'manuals_docs #{docId} has been deleted for duplicate manual_diag #{docNum} on manual #{manualId}',
                            ['docId' => $doc['id'], 'docNum' => $duplicatedDoc['doc_num'], 'manualId' => $duplicatedDoc['parent_id']]
                        );

                        continue;
                    }

                    $logger->warning(
                        'manual_docs #{docId} has not been deleted for duplicate manual_diag #{docNum} on manual #{manualId}',
                        ['docId' => $doc['id'], 'docNum' => $duplicatedDoc['doc_num'], 'manualId' => $duplicatedDoc['parent_id']]
                    );
                }
            }
        }

        return Command::SUCCESS;
    }
}
