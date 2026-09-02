<?php

declare(strict_types=1);

namespace App\Command\Sales\Customer;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;
use App\Manager\Sales\CustomerManager;
use App\Notifier\Sales\Customer\CustomerNotifier;
use App\Repository\Sales\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:sales:customer_reapproval')]
class CustomerToReApprovalStatusCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;
    private readonly CustomerManager $customerManager;
    private readonly CustomerNotifier $customerNotifier;

    public function __construct(EntityManagerInterface $entityManager, CustomerManager $customerManager, CustomerNotifier $customerNotifier)
    {
        parent::__construct();
        $this->setDescription('Set status to PENDING RE-APPROVAL status after 3 years APPROVED');

        $this->entityManager = $entityManager;
        $this->customerManager = $customerManager;
        $this->customerNotifier = $customerNotifier;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->entityManager->getRepository(Customer::class);

        /** @var Customer $customer */
        foreach ($customerRepository->findCustomersToBeReApproved() as $customer) {
            $customer
                ->setStatus(Customer::NOT_ACTIVE)
                ->setValidatedAt(null)
            ;

            $types = $customer->getCustomerTypes()->map(static fn (CustomerType $customerType) => $customerType->getName())->toArray();
            if (!empty(array_intersect($types, CustomerType::THIRD_PARTIES_NAMES))) {
                $customer
                    ->setStatus(Customer::PENDING_RE_APPROVAL)
                    ->setValidatedAt(null)
                    ->setReApprovedAt(new \DateTime())
                ;

                try {
                    $this->customerManager->createAndSendValidationSequence($customer);
                    $output->writeln(\sprintf('Creating sequence for %s', $customer->getName()));
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                    continue;
                }

                $this->customerNotifier->sendThirdPartiesCustomerReApprovalEmail($customer);
            }
            $this->entityManager->persist($customer);
        }

        /** @var Customer $customer */
        foreach ($customerRepository->findCustomersReApprovedMoreThanSixMonthsAgo() as $customer) {
            $customer->setStatus(Customer::PENDING);
            $this->entityManager->persist($customer);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
