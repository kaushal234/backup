<?php

declare(strict_types=1);

namespace App\Command\TLDGroupSync;

use App\Entity\Directory\Network;
use App\Repository\Sales\SalesAreaRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:group:sync:sales_areas')]
class TLDGroupSalesAreasSyncCommand extends Command
{
    private readonly Connection $wordpressConnection;

    private readonly SalesAreaRepository $salesAreaRepository;

    public function __construct(SalesAreaRepository $salesAreaRepository, Connection $wordpressConnection)
    {
        parent::__construct();
        $this->setDescription('Sync Sales Areas to TLD Group Wordpress database');

        $this->wordpressConnection = $wordpressConnection;
        $this->salesAreaRepository = $salesAreaRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $salesAreas = $this->salesAreaRepository->findWithPublicCountry();

        $q = $this->wordpressConnection->getDatabasePlatform()->getTruncateTableSQL('tld_sales_areas');
        $this->wordpressConnection->executeStatement($q);

        $pg = new ProgressBar($output, \count($salesAreas));

        $i = 1;
        foreach ($salesAreas as $salesArea) {
            $pg->advance();

            if (null === ($network = $salesArea->getSso()->getNetwork()) || Network::NETWORK_TLD !== $network->getName()) {
                continue;
            }

            $qb = $this->wordpressConnection->createQueryBuilder();

            $qb
                ->insert('tld_sales_areas')
                ->setValue('id', ':id')
                ->setValue('continent', ':continent')
                ->setValue('country', ':country')
                ->setValue('rep_id', ':asm')
                ->setParameters([
                    'id' => $i,
                    'continent' => null === $salesArea->getCountry()->getContinent() ? '' : $salesArea->getCountry()->getContinent()->getName(),
                    'country' => $salesArea->getCountry()->getName(),
                    'asm' => $salesArea->getAsm()->getLegacyId(),
                ]);

            $this->wordpressConnection->executeQuery($qb->getSQL(), $qb->getParameters());
            ++$i;
        }
        $pg->finish();

        return 0;
    }
}
