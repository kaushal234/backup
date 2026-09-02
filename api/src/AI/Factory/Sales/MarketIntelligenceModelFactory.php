<?php

declare(strict_types=1);

namespace App\AI\Factory\Sales;

use App\AI\Dto\Directory\DivisionModel;
use App\AI\Dto\Sales\CompetitorModel;
use App\AI\Dto\Sales\CustomerModel;
use App\AI\Dto\Sales\MarketIntelligence\MarketIntelligenceModel;
use App\AI\Dto\Sales\MarketIntelligence\MarketIntelligenceTypeModel;
use App\AI\Dto\Sales\ProductTypeModel;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\Entity\Directory\Division;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Entity\Sales\ProductType;

final readonly class MarketIntelligenceModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return MarketIntelligence::class === $class;
    }

    /**
     * @param MarketIntelligence $entity
     */
    public function create(object $entity): MarketIntelligenceModel
    {
        $type = $entity->getType();
        $poster = $entity->getPoster();

        return new MarketIntelligenceModel(
            createdAt: $entity->getCreatedAt(),
            shortDescription: $entity->getShortDescription(),
            description: $entity->getDescription(),
            url: $entity->getUrl(),
            type: null === $type ? null : new MarketIntelligenceTypeModel(name: $type->name),
            poster: null === $poster ? null : $this->peopleModelFactory->create($poster),
            customers: array_values(array_map(
                static fn (Customer $customer) => new CustomerModel(
                    name: $customer->getName(),
                    status: $customer->getStatus(),
                ),
                $entity->getCustomers()->toArray(),
            )),
            competitors: array_values(array_map(
                static fn (Competitor $competitor) => new CompetitorModel(
                    name: $competitor->getName(),
                    shortDescription: $competitor->getShortDescription(),
                    url: $competitor->getUrl(),
                ),
                $entity->getCompetitors()->toArray(),
            )),
            productTypes: array_values(array_map(
                static fn (ProductType $productType) => new ProductTypeModel(name: $productType->getEnglishName()),
                $entity->getProductTypes()->toArray(),
            )),
            divisions: array_values(array_map(
                static fn (Division $division) => new DivisionModel(name: $division->name),
                $entity->getDivisions()->toArray(),
            )),
            suppliers: array_values($entity->getSuppliers()),
        );
    }
}
