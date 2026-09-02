<?php

declare(strict_types=1);

namespace App\Command\Sales\Demo;

use App\Repository\Sales\DemoRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:demo:comment_synchro')]
class DemoSynchronisedCommentCommand extends Command
{
    public function __construct(
        private readonly DemoRepository $demoRepository,
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $demosAsynchronized = $this->connection->executeQuery(<<<'SQL'
                SELECT d.comment, act.message, d.id, act.created_at
                FROM demos d
                LEFT JOIN (
                    SELECT a.id, SUBSTRING(a.resource, 14) AS demoId, a.message, a.created_at
                    FROM activity a
                    INNER JOIN (
                        SELECT MAX(id) AS max_id, SUBSTRING(resource, 14) AS demoId
                        FROM activity
                        WHERE discr = 'comment' AND resource LIKE '%/demos/%'
                        GROUP BY demoId
                    ) AS max_ids ON a.id = max_ids.max_id
                ) AS act ON act.demoId = d.id
                WHERE d.comment != act.message and status in ('SUBMITTED', 'PENDING', 'APPROVED', 'ACTIVE');
            SQL)->fetchAllAssociative();

        foreach ($demosAsynchronized as $demo) {
            $demoToFix = $this->demoRepository->find($demo['id']);
            $demoToFix->setComment($demo['message']);
            $demoToFix->setLastCommentedAt(new \DateTime($demo['created_at']));

            $this->entityManager->persist($demoToFix);
        }
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
