<?php

declare(strict_types=1);

namespace App\AI\Dto\Support;

use App\AI\Dto\Common\AirportModel;
use App\AI\Dto\Common\CountryModel;
use App\AI\Dto\Directory\LocationModel;
use App\AI\Dto\Sales\CustomerModel;
use App\AI\Dto\Sales\ProductModel;
use App\AI\Dto\Sales\ProductTypeModel;

final readonly class EquipmentRecordModel
{
    public function __construct(
        public int $id,
        public int $legacyId,
        public string $serialNumber,
        public ?string $model,
        public ?string $type,
        public ?CustomerModel $buyer = null,
        public ?CustomerModel $endUser = null,
        public ?CustomerModel $maintainer = null,
        public ?ProductModel $product = null,
        public ?ProductTypeModel $productType = null,
        public ?LocationModel $manufacturerLocation = null,
        public ?LocationModel $salesOrganisation = null,
        public ?LocationModel $salesOrganisationService = null,
        public ?AirportModel $airport = null,
        public ?CountryModel $country = null,
        public ?EmissionRatingModel $emissionRating = null,
        public ?string $mainWorkOrder = null,
        public ?string $manufacturingProject = null,
        public ?string $state = null,
        public ?\DateTimeInterface $dateShipped = null,
        public ?string $optionsDescription = null,
    ) {
    }
}
