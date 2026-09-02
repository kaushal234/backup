<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Sales\MainSalesRepresentative;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:synch:main_sales_representative')]
class CustomerMainSalesRepresentativeSynchronizeCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    private readonly Connection $legacyConnection;

    public function __construct(EntityManagerInterface $entityManager, Connection $legacyConnection)
    {
        parent::__construct();
        $this->setDescription('Synchronize TLD legacy customers main sales representatives');
        $this->entityManager = $entityManager;
        $this->legacyConnection = $legacyConnection;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('mainSalesRepresentative')
            ->from(MainSalesRepresentative::class, 'mainSalesRepresentative')
            ->innerJoin('mainSalesRepresentative.asm', 'asm')
            ->innerJoin('mainSalesRepresentative.customer', 'customer')
            ->where('customer.legacyId IS NOT NULL')
            ->andWhere('asm.legacyId IS NOT NULL');

        foreach ($queryBuilder->getQuery()->toIterable() as $mainSalesRepresentative) {
            $this->updateRepresentativeOnLegeacy($mainSalesRepresentative->customer->getLegacyId(), $mainSalesRepresentative->asm->getLegacyId());
        }

        return Command::SUCCESS;
    }

    protected function updateRepresentativeOnLegeacy($customerLegacyId, $asmLegacyId): void
    {
        $sql = <<<'SQL'
            UPDATE customers
            SET asm_id = :asmId
            WHERE id = :customerId
                AND asm_id != :asmId
            SQL;

        $this->legacyConnection->executeQuery($sql, [
            'asmId' => $asmLegacyId,
            'customerId' => $customerLegacyId,
        ]);
    }
}
