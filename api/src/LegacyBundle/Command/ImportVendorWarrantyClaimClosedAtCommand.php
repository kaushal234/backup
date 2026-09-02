<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Purchasing\VendorWarrantyClaim;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:vwc_closed_at')]
class ImportVendorWarrantyClaimClosedAtCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly EntityCacheHelperFactory $cacheFactory;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(Connection $legacyConnection, EntityCacheHelperFactory $cacheFactory, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Imports VWC closed At from legacy');
        $this->legacyConnection = $legacyConnection;
        $this->cacheFactory = $cacheFactory;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import VWC closing date
        $sql = <<<'SQL'
            SELECT date, parent_id
            FROM mod_logs m
            WHERE m.module='VWC' AND m.comment LIKE 'Status CLOSED%'
            SQL;
        $results = $this->legacyConnection->executeQuery($sql)->fetchAllAssociative();

        $vwcCache = $this->cacheFactory->createEntityCache(VendorWarrantyClaim::class, 'id');

        $pg = new ProgressBar($output, \count($results));

        /* @var VendorWarrantyClaim $vendorWarrantyClaim */
        foreach ($results as $result) {
            $pg->advance();

            $vendorWarrantyClaim = $vwcCache->fetch($result['parent_id']);
            if (!$vendorWarrantyClaim instanceof VendorWarrantyClaim) {
                continue;
            }
            $vendorWarrantyClaim->closedAt = new \DateTime($result['date']);
            $this->entityManager->persist($vendorWarrantyClaim);
        }

        $this->entityManager->flush();

        $pg->finish();

        return Command::SUCCESS;
    }
}
