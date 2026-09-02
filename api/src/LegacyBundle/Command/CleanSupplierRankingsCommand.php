<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\Location;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Manager\Purchasing\SupplierRanking\SupplierRankingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:purchasing:supplier_rankings:clean')]
class CleanSupplierRankingsCommand extends Command
{
    private readonly SupplierRankingManager $supplierRankingManager;

    private readonly EntityManagerInterface $entityManager;

    public function __construct(
        SupplierRankingManager $supplierRankingManager,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Verify data from LN and delete unused rankings.');
        $this->supplierRankingManager = $supplierRankingManager;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        throw new \LogicException('This command should not be use anymore.');
        /*foreach ($this->entityManager->getRepository(Location::class)->findAll() as $location) {
            if (null !== $location->getErp()) {
                foreach ($this->supplierRankingManager->findTurnoverWithSite($location->getErp(), 10) as $turnover) {
                    $supplierRanking = $this->entityManager->getRepository(SupplierRanking::class)->createQueryBuilder('sr')
                        ->innerJoin('sr.location', 'location')
                        ->where('sr.supplierNumber = :supplierNumber')
                        ->setParameter('supplierNumber', $turnover->code)
                        ->andWhere('location.erp = :erpCode')
                        ->setParameter('erpCode', $location->getErp())
                        ->getQuery()->getOneOrNullResult();

                    if ($supplierRanking instanceof SupplierRanking) {
                        $supplierRanking->isValid = true;
                    }
                }
            }
            $this->entityManager->flush();
        }

        $this->entityManager->getRepository(SupplierRanking::class)->createQueryBuilder('sr')
            ->delete()
            ->where('sr.isValid = false')
            ->getQuery()
            ->execute();

        return Command::SUCCESS;*/
    }
}
