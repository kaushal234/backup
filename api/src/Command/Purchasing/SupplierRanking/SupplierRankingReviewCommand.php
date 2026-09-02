<?php

declare(strict_types=1);

namespace App\Command\Purchasing\SupplierRanking;

use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Notifier\Purchasing\SupplierRanking\SupplierRankingNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:supplier_ranking:review', description: 'Send notifications to users when a supplier ranking need a review.')]
class SupplierRankingReviewCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SupplierRankingNotifier $notifier
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queryBuilder = $this->entityManager->getRepository(SupplierRanking::class)->createQueryBuilder('supplierRanking');

        /** @var SupplierRanking $supplierRanking */
        foreach ($queryBuilder->getQuery()->toIterable() as $supplierRanking) {
            $nextReviewDate = $supplierRanking->getNextReviewAt();
            if (null !== $nextReviewDate
                && $nextReviewDate <= new \DateTime()
                && null === $supplierRanking->disabledAt) {
                $this->notifier->sendReviewNotification($supplierRanking);
            }
        }

        return Command::SUCCESS;
    }
}
