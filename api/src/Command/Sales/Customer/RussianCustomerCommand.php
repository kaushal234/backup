<?php

declare(strict_types=1);

namespace App\Command\Sales\Customer;

use App\Entity\Country;
use App\Entity\Sales\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:customers:russian')]
class RussianCustomerCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->setDescription('Disapprove russian customers');
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $customerRepository = $this->entityManager->getRepository(Customer::class);

        $russia = $this->entityManager->getRepository(Country::class)->find(180);

        foreach ($customerRepository->findBy(['country' => $russia]) as $customer) {
            if (Customer::PENDING_RE_APPROVAL === $customer->getStatus()) {
                continue;
            }

            $customer->setStatus(Customer::NOT_APPROVED);
            $logger->info('Customer {customer} is now NOT_APPROVED', ['customer' => $customer->getName()]);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
