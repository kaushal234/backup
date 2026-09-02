<?php

declare(strict_types=1);

namespace App\Command\Sales\Customer;

use App\Command\Sales\Order\SalesOrdersBaanBpCodeMappingCommand;
use App\Entity\Sales\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'customers:bp-code:map')]
class CustomerBusinessPartnerCodesMappingCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Set inforLnBusinessPartnerCodes property for customers');
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $customerRepository = $this->entityManager->getRepository(Customer::class);

        $customerUpdatedCount = 0;
        foreach ($customerRepository->findAll() as $customer) {
            $key = $customer->getId();
            if (\array_key_exists($key, SalesOrdersBaanBpCodeMappingCommand::CUNO_BP_CODES_MAPPING)) {
                ++$customerUpdatedCount;
                $customer->setInforLnBusinessPartnerCodes(array_unique(array_merge($customer->getInforLnBusinessPartnerCodes(), [SalesOrdersBaanBpCodeMappingCommand::CUNO_BP_CODES_MAPPING[$key]])));
                $this->entityManager->persist($customer);
            }
        }
        $this->entityManager->flush();
        $output->writeln(\sprintf('Assigned Business Partner Codes From LN to %s eCustomers', $customerUpdatedCount));

        return Command::SUCCESS;
    }
}
