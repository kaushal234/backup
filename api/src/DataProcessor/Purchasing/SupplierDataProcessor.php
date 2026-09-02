<?php

declare(strict_types=1);

namespace App\DataProcessor\Purchasing;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Purchasing\Supplier\SupplierInput;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Finance\Currency;
use App\Entity\Purchasing\Supplier\Supplier;
use App\Entity\Purchasing\Supplier\SupplierLocation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @template T
 */
class SupplierDataProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param SupplierInput $data
     *
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $locationRepository = $this->entityManager->getRepository(Location::class);
        $supplier = $this->entityManager->getRepository(Supplier::class)->findOneBy(['code' => $data->code]);

        if (null === $supplier) {
            $supplier = new Supplier();
        }

        $supplier->name = $data->name;
        $supplier->code = $data->code;
        $supplier->currency = null;
        $supplier->location = null;
        $supplier->country = null;

        if ($data->status) {
            $supplier->status = $data->status;
        }

        if ($data->currency) {
            $supplier->currency = $this->entityManager
                ->getRepository(Currency::class)
                ->findOneBy(['name' => $data->currency])
            ;
        }

        if ($data->masterBusinessUnit) {
            $supplier->location = $locationRepository->findOneBy(['erp' => $data->masterBusinessUnit]);
        }

        if ($data->country) {
            $supplier->country = $this->entityManager
                ->getRepository(Country::class)
                ->findOneBy(['isoCode2' => $data->country])
            ;
        }

        // Remove locations not in buyFrom
        foreach ($supplier->getBuyFrom() as $buyFrom) {
            $found = false;

            // Looking for supplierLocation exists in the supplier to not remove them.
            foreach ($data->buyFrom as $supplierLocationInput) {
                if ($buyFrom->location->getErp() === (int) $supplierLocationInput->locationCode) {
                    if ($supplierLocationInput->buyerId && $supplierLocationInput->buyerId !== $buyFrom->buyer?->getId()) {
                        $buyFrom->buyer = $this->entityManager
                            ->getRepository(People::class)
                            ->find($supplierLocationInput->buyerId)
                        ;
                    }

                    $found = true;
                }
            }

            if ($found) {
                continue;
            }

            $supplier->removeBuyFrom($buyFrom);
        }

        // Add news one
        foreach ($data->buyFrom as $supplierLocationInput) {
            $found = false;

            // Do not include the supplier location already in the collection.
            foreach ($supplier->getBuyFrom() as $buyFrom) {
                if ($buyFrom->location->getErp() === (int) $supplierLocationInput->locationCode) {
                    $found = true;
                }
            }

            if ($found) {
                continue;
            }

            // Create the new supplierLocation and and it to the collection.
            $supplierLocation = new SupplierLocation();
            $supplierLocation->supplier = $supplier;

            if ($supplierLocationInput->buyerId) {
                $supplierLocation->buyer = $this->entityManager
                    ->getRepository(People::class)
                    ->find($supplierLocationInput->buyerId)
                ;
            }

            if ($supplierLocationInput->locationCode) {
                $supplierLocation->location = $locationRepository->findOneBy(['erp' => $supplierLocationInput->locationCode]);
            }
            $supplier->addBuyFrom($supplierLocation);
            $this->entityManager->persist($supplierLocation);
        }

        return $this->persistProcessor->process($supplier, $operation);
    }
}
