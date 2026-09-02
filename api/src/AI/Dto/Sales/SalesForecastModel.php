<?php

declare(strict_types=1);

namespace App\AI\Dto\Sales;

use App\AI\Dto\Common\AirportModel;
use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Directory\PeopleModel;

final readonly class SalesForecastModel
{
    public function __construct(
        public string $status,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?\DateTimeInterface $lastCommentedAt,
        public ?\DateTimeInterface $closedAt,
        public ?string $equoteId,
        public int $quantity,
        public \DateTimeInterface $estimatedSaleDate,
        public int $customerSuccessPercentage,
        public int $successPercentage,
        public bool $delinquent,
        public ?int $price,
        public ?float $margin,
        public LocationModel $sso,
        public LocationModel $factory,
        public PeopleModel $asm,
        public PeopleModel $poster,
        public ?CustomerModel $buyer,
        public ?CustomerModel $endUser,
        public ?CustomerModel $thirdParty,
        public ?string $country,
        public ?AirportModel $airport,
        public ?ProductModel $product,
        public ?string $tier,
        public ?QuoteModel $quote,
    ) {
    }
}
