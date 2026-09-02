<?php

declare(strict_types=1);

namespace App\Command\Sales\Customer;

use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerType;
use App\Repository\Sales\CustomerRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(name: 'tld:customers:types')]
class CustomerTypeCommand extends Command
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this
            ->setDescription('Add or remove (with --delete flag) a customer type')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the customer type that will be created / deleted')
            ->addOption('delete', 'd', InputOption::VALUE_OPTIONAL, 'switch to delete mode', false)
        ;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $customerTypeRepository = $this->entityManager->getRepository(CustomerType::class);

        /** @var string $name */
        $name = $input->getArgument('name');

        if (false !== $input->getOption('delete')) {
            $type = $customerTypeRepository->findOneBy(['name' => $name]);
            if (!$type instanceof CustomerType) {
                $output->writeln(\sprintf("<error>Couldn't find any customer type matching \"%s\"</error>", $name));

                return 1;
            }
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                \sprintf(
                    '<question>Are you sure you want to delete type "%s" ID #%d?</question>',
                    $type->getName(),
                    $type->getId()
                ),
                false,
                '/^(y)/i'
            );

            if (!$helper->ask($input, $output, $question)) {
                return 0;
            }

            /** @var CustomerRepository $customerRepository */
            $customerRepository = $this->entityManager->getRepository(Customer::class);
            $customers = $customerRepository->findCustomersByType($type);

            /** @var Customer $customer */
            foreach ($customers as $customer) {
                $customer->removeCustomerType($type);
                $this->entityManager->persist($customer);
            }

            $this->entityManager->remove($type);
            $this->entityManager->flush();

            $output->writeln(\sprintf('<info>Customer type "%s" removed</info>', $name));

            return 0;
        }

        $newType = new CustomerType();
        $newType->setName($name);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            \sprintf(
                '<question>Are you sure you want to insert the new customer type "%s"?</question>',
                $name
            ),
            false,
            '/^(y)/i'
        );

        if (!$helper->ask($input, $output, $question)) {
            return 0;
        }

        try {
            $this->entityManager->persist($newType);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $uniqueConstraintViolationException) {
            $output->writeln(\sprintf('<error>Customer type "%s" already exists</error>', $name));

            return 1;
        }

        $output->writeln(\sprintf('<info>Customer type "%s" created with ID #%d</info>', $name, $newType->getId()));

        return 0;
    }
}
