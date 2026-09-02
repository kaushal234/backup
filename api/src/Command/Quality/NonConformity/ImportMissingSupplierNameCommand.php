<?php

declare(strict_types=1);

namespace App\Command\Quality\NonConformity;

use App\Entity\Quality\NonConformity;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:ncr:supplier', description: 'Import missing supplier names in NCR')]
class ImportMissingSupplierNameCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BusinessPartnerManager $businessPartnerManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nonConformityRepository = $this->entityManager->getRepository(NonConformity::class);
        $count = 0;
        foreach ($nonConformityRepository->findAll() as $nonConformity) {
            if (null === $nonConformity->getSupplierNumber() || (null !== $nonConformity->getSupplierNumber() && null !== $nonConformity->getSupplierName())) {
                continue;
            }

            foreach ($nonConformityRepository->findBy(['supplierNumber' => $nonConformity->getSupplierNumber()]) as $existingNonConformity) {
                if (null === $existingNonConformity->getSupplierName()) {
                    continue;
                }

                $nonConformity->setSupplierName($existingNonConformity->getSupplierName());
                $output->writeln(\sprintf('NCR #%d updated.', $nonConformity->getId()));
                ++$count;
                continue 2;
            }

            $businessPartner = $this->businessPartnerManager->findSupplier($nonConformity->getSupplierNumber());

            if (null === $businessPartner) {
                continue;
            }

            $nonConformity->setSupplierName($businessPartner->name);
            $output->writeln(\sprintf('NCR #%d updated.', $nonConformity->getId()));
            ++$count;
        }

        $this->entityManager->flush();
        $output->writeln(\sprintf('%d NCRs updated.', $count));

        return Command::SUCCESS;
    }
}
