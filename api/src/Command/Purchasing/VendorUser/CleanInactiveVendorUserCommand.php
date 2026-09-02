<?php

declare(strict_types=1);

namespace App\Command\Purchasing\VendorUser;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Purchasing\VendorUser;
use App\ION\DataProvider\IONItemDataProvider;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContactCategory;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:vendor-user:clean-inactive', description: 'Clean inactive vendor users')]
class CleanInactiveVendorUserCommand extends Command
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly IONItemDataProvider $itemDataProvider,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $operation = $this->resourceMetadataCollectionFactory->create(BusinessPartnerContact::class)->getOperation();
        $vendorUsers = $this->entityManager->getRepository(VendorUser::class)->findAll();

        foreach ($vendorUsers as $vendorUser) {
            if (null === $vendorUser->getErpIdentifier()) {
                continue;
            }
            try {
                /** @var BusinessPartnerContact $LNVendorUser */
                $LNVendorUser = $this->itemDataProvider->provide($operation, [
                    'contactCode' => $vendorUser->getErpIdentifier(),
                ]);
                /** @var Collection $categories */
                $categories = $LNVendorUser->getCategories()->filter(static fn (BusinessPartnerContactCategory $category) => 'PUR-EV' === $category->code);
                if (!$categories->isEmpty()) {
                    continue;
                }

                $vendorUser->setDisabled(true);
                $vendorUser->setHidden(true);
                $output->writeln(\sprintf('User #%d is disabled and hidden', $vendorUser->getId()));
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->entityManager->flush();

        return 0;
    }
}
