<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales\MarketIntelligence;

use App\AI\Dto\Directory\DivisionModel;
use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Sales\CompetitorModel;
use App\AI\Dto\Sales\CustomerModel;
use App\AI\Dto\Sales\ProductTypeModel;

final readonly class MarketIntelligenceModel
{
    /**
     * @param list<CustomerModel>    $customers
     * @param list<CompetitorModel>  $competitors
     * @param list<ProductTypeModel> $productTypes
     * @param list<DivisionModel>    $divisions
     * @param list<string>           $suppliers
     */
    public function __construct(
        public \DateTimeInterface $createdAt,
        public string $shortDescription,
        public string $description,
        public ?string $url,
        public ?MarketIntelligenceTypeModel $type,
        public ?PeopleModel $poster,
        public array $customers,
        public array $competitors,
        public array $productTypes,
        public array $divisions,
        public array $suppliers,
    ) {
    }
}
