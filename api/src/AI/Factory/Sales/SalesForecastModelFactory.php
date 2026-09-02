<?php

declare(strict_types=1);

namespace App\AI\Factory\Sales;

use App\AI\Dto\Common\AirportModel;
use App\AI\Dto\Sales\CustomerModel;
use App\AI\Dto\Sales\ProductModel;
use App\AI\Dto\Sales\QuoteModel;
use App\AI\Dto\Sales\SalesForecastModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\Common\Airport;
use App\Entity\Sales\Customer;
use App\Entity\Sales\SalesForecast;

final readonly class SalesForecastModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return SalesForecast::class === $class;
    }

    /**
     * @param SalesForecast $entity
     */
    public function create(object $entity): SalesForecastModel
    {
        $buyer = $entity->getBuyer();
        $endUser = $entity->getEndUser();
        $thirdParty = $entity->getThirdParty();
        $country = $entity->getCountry();
        $airport = $entity->getAirport();
        $product = $entity->getProduct();
        $tier = $entity->getTier();
        $quote = $entity->getQuote();

        return new SalesForecastModel(
            status: $entity->getStatus(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            lastCommentedAt: $entity->getLastCommentedAt(),
            closedAt: $entity->getClosedAt(),
            equoteId: $entity->getEquoteId(),
            quantity: $entity->getQuantity(),
            estimatedSaleDate: $entity->getEstimatedSaleDate(),
            customerSuccessPercentage: $entity->getCustomerSuccessPercentage(),
            successPercentage: $entity->getSuccessPercentage(),
            delinquent: $entity->isDelinquent(),
            price: $entity->getPrice(),
            margin: $entity->getMargin(),
            sso: $this->locationModelFactory->create($entity->getSso()),
            factory: $this->locationModelFactory->create($entity->getFactory()),
            asm: $this->peopleModelFactory->create($entity->getAsm()),
            poster: $this->peopleModelFactory->create($entity->getPoster()),
            buyer: null === $buyer ? null : $this->createCustomer($buyer),
            endUser: null === $endUser ? null : $this->createCustomer($endUser),
            thirdParty: null === $thirdParty ? null : $this->createCustomer($thirdParty),
            country: $country?->getName(),
            airport: null === $airport ? null : $this->createAirport($airport),
            product: null === $product ? null : new ProductModel(
                name: $product->getName(),
                family: $product->getFamily()->getName(),
            ),
            tier: $tier?->getName(),
            quote: null === $quote ? null : new QuoteModel(quoteNumber: $quote->quoteNumber),
        );
    }

    private function createCustomer(Customer $customer): CustomerModel
    {
        return new CustomerModel(
            name: $customer->getName(),
            status: $customer->getStatus(),
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
}
