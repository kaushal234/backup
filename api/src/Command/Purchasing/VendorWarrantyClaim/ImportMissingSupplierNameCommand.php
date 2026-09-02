<?php

declare(strict_types=1);

namespace App\Command\Purchasing\VendorWarrantyClaim;

use App\Entity\Purchasing\VendorWarrantyClaim;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:vwc:supplier', description: 'Import missing supplier names in VWC')]
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
        $vendorWarrantyClaimRepository = $this->entityManager->getRepository(VendorWarrantyClaim::class);
        $count = 0;
        foreach ($vendorWarrantyClaimRepository->findAll() as $vendorWarrantyClaim) {
            if (null === $vendorWarrantyClaim->getSupplierNumber() || (null !== $vendorWarrantyClaim->getSupplierNumber() && null !== $vendorWarrantyClaim->getSupplierName())) {
                continue;
            }

            foreach ($vendorWarrantyClaimRepository->findBy(['supplierNumber' => $vendorWarrantyClaim->getSupplierNumber()]) as $existingVendorWarrantyClaim) {
                if (null === $existingVendorWarrantyClaim->getSupplierName()) {
                    continue;
                }

                $vendorWarrantyClaim->setSupplierName($existingVendorWarrantyClaim->getSupplierName());
                $output->writeln(\sprintf('VWC #%d updated.', $vendorWarrantyClaim->getId()));
                ++$count;
                continue 2;
            }

            $businessPartner = $this->businessPartnerManager->findSupplier($vendorWarrantyClaim->getSupplierNumber());

            if (null === $businessPartner) {
                continue;
            }

            $vendorWarrantyClaim->setSupplierName($businessPartner->name);
            $output->writeln(\sprintf('VWC #%d updated.', $vendorWarrantyClaim->getId()));
            ++$count;
        }

        $this->entityManager->flush();
        $output->writeln(\sprintf('%d VWCs updated.', $count));

        return Command::SUCCESS;
    }
}
