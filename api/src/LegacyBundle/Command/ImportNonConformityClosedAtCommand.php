<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Quality\NonConformity;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:quality:ncr_closed_at')]
class ImportNonConformityClosedAtCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports NCR closed At from legacy');
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import NCR closing date
        $sql = <<<'SQL'
            SELECT date, parent_id, comment
            FROM mod_logs m
            WHERE m.module='NCR' AND m.comment LIKE '%to CLOSED%'
            SQL;
        $results = $this->legacyConnection->executeQuery($sql)->fetchAllAssociative();
        $ncrCache = $this->cacheFactory->createEntityCache(NonConformity::class, 'id');
        $pg = new ProgressBar($output, \count($results));
        /* @var NonConformity $nonConformity */
        foreach ($results as $result) {
            $pg->advance();

            /** @var NonConformity|null $nonConformity */
            $nonConformity = $ncrCache->fetch($result['parent_id']);
            if (!$nonConformity instanceof NonConformity) {
                continue;
            }
            $nonConformity->closedAt = new \DateTime($result['date']);
            $this->entityManager->persist($nonConformity);
        }

        $this->entityManager->flush();

        $pg->finish();

        return Command::SUCCESS;
    }
}
