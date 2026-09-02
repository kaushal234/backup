<?php

declare(strict_types=1);

namespace App\AI\Factory\Support;

use App\AI\Dto\Common\AirportModel;
use App\AI\Dto\Common\CountryModel;
use App\AI\Dto\Sales\CustomerModel;
use App\AI\Dto\Sales\ProductModel;
use App\AI\Dto\Sales\ProductTypeModel;
use App\AI\Dto\Support\EmissionRatingModel;
use App\AI\Dto\Support\EquipmentRecordModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\Common\Airport;
use App\Entity\EmissionRating;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Product;

final readonly class EquipmentRecordModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return EquipmentRecord::class === $class;
    }

    /**
     * @param EquipmentRecord $entity
     */
    public function create(object $entity): EquipmentRecordModel
    {
        $buyer = $entity->getBuyer();
        $endUser = $entity->getEndUser();
        $maintainer = $entity->getMaintainer();
        $product = $entity->getProduct();
        $manufacturerLocation = $entity->getManufacturerLocation();
        $salesOrganisation = $entity->getSalesOrganisation();
        $salesOrganisationService = $entity->getSalesOrganisationService();
        $airport = $entity->getAirport();
        $country = $entity->getDeliveredCountry();
        $emissionRating = $entity->getEmissionRating();

        return new EquipmentRecordModel(
            id: $entity->getId(),
            legacyId: $entity->getLegacyId(),
            serialNumber: $entity->getSerialNumber(),
            model: $entity->getModel(),
            type: $entity->getType(),
            buyer: null === $buyer ? null : $this->createCustomer($buyer),
            endUser: null === $endUser ? null : $this->createCustomer($endUser),
            maintainer: null === $maintainer ? null : $this->createCustomer($maintainer),
            product: null === $product ? null : $this->createProduct($product),
            productType: null === $product ? null : new ProductTypeModel(
                name: $product->getFamily()->getProductType()->getEnglishName(),
            ),
            manufacturerLocation: null === $manufacturerLocation ? null : $this->locationModelFactory->create($manufacturerLocation),
            salesOrganisation: null === $salesOrganisation ? null : $this->locationModelFactory->create($salesOrganisation),
            salesOrganisationService: null === $salesOrganisationService ? null : $this->locationModelFactory->create($salesOrganisationService),
            airport: null === $airport ? null : $this->createAirport($airport),
            country: null === $country ? null : new CountryModel(name: $country->getName()),
            emissionRating: null === $emissionRating ? null : $this->createEmissionRating($emissionRating),
            mainWorkOrder: $entity->getWorkOrder(),
            manufacturingProject: $entity->getProjectNumber(),
            state: $entity->getState(),
            dateShipped: $entity->getDateShipped(),
            optionsDescription: $entity->getOptionsDescription(),
        );
    }

    private function createCustomer(Customer $customer): CustomerModel
    {
        return new CustomerModel(
            name: $customer->getName(),
            status: $customer->getStatus(),
        );
    }

    private function createProduct(Product $product): ProductModel
    {
        return new ProductModel(
            name: $product->getName(),
            family: $product->getFamily()->getName(),
        );
    }

    private function createAirport(Airport $airport): AirportModel
    {
        return new AirportModel(
            code: $airport->getCode(),
            type: $airport->getType(),
            cityCode3: $airport->getCityCode3(),
            cityName: $airport->getCityName(),
            state: $airport->getState(),
            country: $airport->getCountry()?->getName(),
            name: $airport->getName(),
            source: $airport->getSource(),
            latitude: $airport->getLatitude(),
            longitude: $airport->getLongitude(),
        );
    }

    private function createEmissionRating(EmissionRating $emissionRating): EmissionRatingModel
    {
        return new EmissionRatingModel(name: $emissionRating->getName());
    }
}
