<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Support\EquipmentRecord\HourMeterTransaction\CustomerServiceRecordHourMeterTransaction;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:csr:hour_meter:update')]
class CSRHourMeterUpdateCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly EntityCacheHelperFactory $cacheHelperFactory;

    /**
     * CountriesASMCommand constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, EntityCacheHelperFactory $entityCacheHelperFactory)
    {
        parent::__construct();
        $this->setDescription('Create link between CSR and CSR Hour Meter transaction ');

        $this->entityManager = $entityManager;
        $this->cacheHelperFactory = $entityCacheHelperFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $csrCache = $this->cacheHelperFactory->createEntityCache(AbstractCustomerServiceRecord::class, 'legacyId');

        $transactions = $this->entityManager->getRepository(CustomerServiceRecordHourMeterTransaction::class)->findAll();
        $progressBar = new ProgressBar($output, \count($transactions));
        $batch = 0;
        foreach ($transactions as $transaction) {
            $customerServiceRecord = $csrCache->fetch((string) $transaction->getCustomerServiceRecordLegacyId());

            if (null === $customerServiceRecord) {
                $this->entityManager->remove($transaction);
                $progressBar->advance();
                continue;
            }

            $transaction->setCustomerServiceRecord($customerServiceRecord);

            $this->entityManager->persist($transaction);

            ++$batch;
            if (5000 === $batch) {
                $this->entityManager->flush();
            }
            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();

        return Command::SUCCESS;
    }
}
