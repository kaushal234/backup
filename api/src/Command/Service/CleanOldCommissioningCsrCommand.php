<?php

declare(strict_types=1);

namespace App\Command\Service;

use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:csr:clean-old-commissioning',
    description: 'Delete old CSR COMMISSIONING (PENDING/PLANNED/ASSIGNED) prior to 04/30/2026.',
)]
class CleanOldCommissioningCsrCommand extends Command
{
    private const CUTOFF_DATE = '2026-04-30 23:59:59';
    private const STATUSES = ['PENDING', 'PLANNED', 'ASSIGNED'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $batchSize = 200;

        $filters = $this->entityManager->getFilters();
        if (!$filters->isEnabled('softdeleteable')) {
            $filters->enable('softdeleteable');
        }

        $repo = $this->entityManager->getRepository(CommissioningCustomerServiceRecord::class);

        $qb = $repo->createQueryBuilder('c')
            ->where('c.status IN (:statuses)')
            ->andWhere('c.createdAt <= :cutoff')
            ->setParameter('statuses', self::STATUSES)
            ->setParameter('cutoff', new \DateTimeImmutable(self::CUTOFF_DATE))
        ;

        $totalCount = (clone $qb)
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if (0 === $totalCount) {
            $io->success('No CSRs found for deletion.');

            return Command::SUCCESS;
        }

        $io->progressStart($totalCount);

        $deleted = 0;

        while (true) {
            $batch = (clone $qb)
                ->setMaxResults($batchSize)
                ->getQuery()
                ->getResult();

            if (0 === \count($batch)) {
                break;
            }

            foreach ($batch as $csr) {
                $this->entityManager->remove($csr);
                ++$deleted;
                $io->progressAdvance();
            }

            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        $io->progressFinish();
        $io->success(\sprintf('%d COMMISSIONING CSRs successfully soft-deleted.', $deleted));

        return Command::SUCCESS;
    }
}
