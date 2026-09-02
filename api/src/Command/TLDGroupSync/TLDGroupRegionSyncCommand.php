<?php

declare(strict_types=1);

namespace App\Command\TLDGroupSync;

use App\Entity\Directory\Region;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:group:sync:regions')]
class TLDGroupRegionSyncCommand extends Command
{
    // todo #divisionproject update crontab after release
    private readonly Connection $wordpressConnection;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, Connection $wordpressConnection)
    {
        parent::__construct();
        $this->setDescription('Sync Regions to TLD Group Wordpress database');

        $this->wordpressConnection = $wordpressConnection;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $regions = $this->entityManager->getRepository(Region::class)->findAll();
        $q = $this->wordpressConnection->getDatabasePlatform()->getTruncateTableSQL('tld_division');
        $this->wordpressConnection->executeStatement($q);

        $pg = new ProgressBar($output, \count($regions));

        foreach ($regions as $region) {
            $pg->advance();

            $qb = $this->wordpressConnection->createQueryBuilder();

            $qb
                ->insert('tld_division')
                ->setValue('id', ':id')
                ->setValue('division', ':division')
                ->setParameters([
                    'id' => $region->getLegacyId(),
                    'division' => $region->getName(),
                ])
            ;

            $this->wordpressConnection->executeQuery($qb->getSQL(), $qb->getParameters());
        }
        $pg->finish();

        return 0;
    }
}
