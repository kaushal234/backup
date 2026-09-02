<?php

declare(strict_types=1);

namespace App\Command;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Country;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Repository\Sales\CustomerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:transfer:customer')]
class TransferCustomerCommand extends Command
{
    private readonly IriConverterInterface $iriConverter;
    private readonly CustomerRepository $customerRepository;

    public function __construct(IriConverterInterface $iriConverter, CustomerRepository $customerRepository)
    {
        parent::__construct();
        $this
            ->setDescription('Transfer a customer from an ASM to another one')
            ->addArgument(
                'from',
                InputArgument::REQUIRED,
                'ASM of the customer you want to transfer'
            )
            ->addArgument(
                'to',
                InputArgument::REQUIRED,
                'Target ASM you want to transfer to'
            )
            ->addOption(
                'country',
                'ctry',
                InputOption::VALUE_REQUIRED,
                'The country you want to filter with'
            )
        ;
        $this->iriConverter = $iriConverter;
        $this->customerRepository = $customerRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            /** @var string $from */
            $from = $input->getArgument('from');
            /** @var People $asm */
            $asm = $this->iriConverter->getResourceFromIri($from);
            /** @var string $to */
            $to = $input->getArgument('to');
            /** @var People $target */
            $target = $this->iriConverter->getResourceFromIri($to);
        } catch (InvalidArgumentException $invalidArgumentException) {
            $output->writeln(\sprintf('<error>Invalid People submitted (%s)</error>', $invalidArgumentException->getMessage()));

            return 1;
        }

        /** @var string|null $countryIri */
        $countryIri = $input->getOption('country');
        $country = null;
        if (null !== $countryIri) {
            try {
                /** @var Country $country */
                $country = $this->iriConverter->getResourceFromIri($countryIri);
            } catch (InvalidArgumentException $invalidArgumentException) {
                $output->writeln(\sprintf('<error>Invalid Country submitted (%s)</error>', $invalidArgumentException->getMessage()));

                return 1;
            }
        }

        /** @var Customer[] $customers */
        $customers = $this->customerRepository->getCustomersByMainRepresentativeCountry($asm, $country);

        if ([] === $customers) {
            $output->writeln('No customer to transfer');

            return 0;
        }

        if (null === $target->getBusinessUnit()->getRegion()->getSubDivision()) {
            $output->writeln('Target does not have any subdivision set, transfer not possible.');

            return 0;
        }

        $this->customerRepository->changeCustomerMainRepresentative($customers, $target);

        $output->writeln(\sprintf('<info>%d customers transferred</info>', \count($customers)));

        return 0;
    }
}
