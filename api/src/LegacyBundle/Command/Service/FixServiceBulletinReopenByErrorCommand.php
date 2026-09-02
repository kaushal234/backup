<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sb:line:fix:status',
    description: 'Update to close sb line with CSR closed.',
)]
class FixServiceBulletinReopenByErrorCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $legacyConnection
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->select('sbl.id as sb_line_id, sb.dt, sbl.spr_id, sbl.status, sb.id as sb_id, er.sn as er_sn, er.sso_service, csr.status csr_status')
            ->from('sb_lines', 'sbl')
            ->innerJoin('sbl', 'sb', 'sb', 'sbl.parent_id = sb.id')
            ->innerJoin('sbl', 'service', 'er', 'sbl.er_id = er.id')
            ->innerJoin('sbl', 'csr', 'csr', 'sbl.csr_id = csr.id')
            ->where('sbl.status != :sbl_status')
            ->andWhere("csr.status IN ('CLOSED', 'COMPLETED')")
            ->orderBy('er.sso_service')
            ->groupBy('sbl.id')
            ->setParameter('sbl_status', 'CLOSED');

        $sbLines = $this->legacyConnection
            ->executeQuery($qb->getSQL(), $qb->getParameters())
            ->fetchAllAssociative();

        $updated = 0;
        foreach ($sbLines as $sbLine) {
            $this->legacyConnection->update(
                'sb_lines',
                ['status' => 'CLOSED'],
                ['id' => $sbLine['sb_line_id']]
            );

            ++$updated;
            $output->writeln(\sprintf(
                'SB line %s from SB %s with ER %s has been closed.',
                $sbLine['sb_line_id'],
                $sbLine['sb_id'],
                $sbLine['er_sn']
            ));
        }

        $output->writeln('');
        $output->writeln(\sprintf(
            '%d SB lines have been updated to CLOSED.',
            $updated
        ));

        return Command::SUCCESS;
    }
}
