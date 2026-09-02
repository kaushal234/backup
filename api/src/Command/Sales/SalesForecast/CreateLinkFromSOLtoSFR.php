<?php

declare(strict_types=1);

namespace App\Command\Sales\SalesForecast;

use Doctrine\DBAL\Connection;
use LegacyBundle\Manager\ModLinkManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:sol:links')]
class CreateLinkFromSOLtoSFR extends Command
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly ModLinkManager $manager,
    ) {
        parent::__construct();
        $this->setDescription('Create missing link between SOL and SFR in legacy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $sols = $queryBuilder
            ->select('*')
            ->from('sor_lines')
            ->where('sor_lines.sfr_id IS NOT NULL')
            ->andWhere('sor_lines.sfr_id <> 0')
            ->andWhere('sor_lines.sfr_id <> ""')
            ->executeQuery()
            ->fetchAllAssociative()
        ;

        $i = 0;
        foreach ($sols as $sol) {
            $linkFromSOLQueryBuilder = $this->legacyConnection->createQueryBuilder();
            $linkFromSOLQueryBuilder
                ->select('parent_id')
                ->from('mod_links')
                ->where('module = :sol')
                ->andWhere('type = :sfr')
                ->setParameter('sol', 'SOL')
                ->setParameter('sfr', 'SFR2')
            ;

            $linksFromSOL = $linkFromSOLQueryBuilder->executeQuery()->fetchAllAssociative();
            foreach ($linksFromSOL as $linkFromSOL) {
                if ((int) $sol['id'] === (int) $linkFromSOL['parent_id']) {
                    continue 2;
                }
            }

            $linkToSOLQueryBuilder = $this->legacyConnection->createQueryBuilder();
            $linkToSOLQueryBuilder
                ->select('item')
                ->from('mod_links')
                ->where('module = :sfr')
                ->andWhere('type = :sol')
                ->setParameter('sol', 'SOL')
                ->setParameter('sfr', 'SFR2')
            ;

            $linksToSOL = $linkToSOLQueryBuilder->executeQuery()->fetchAllAssociative();
            foreach ($linksToSOL as $linkToSOL) {
                if ((int) $sol['id'] === (int) $linkToSOL['item']) {
                    continue 2;
                }
            }

            $this->manager->createLink((int) $sol['id'], 'SOL', (int) $sol['sfr_id'], 'SFR2');
            ++$i;
            $output->writeln(\sprintf('Link created between SOL#%d and SFR#%d', (int) $sol['id'], (int) $sol['sfr_id']));
        }

        $output->writeln(\sprintf('%d links created', $i));

        return Command::SUCCESS;
    }
}
