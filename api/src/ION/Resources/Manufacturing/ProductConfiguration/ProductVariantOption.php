<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\ProductConfiguration;

use Symfony\Component\Serializer\Attribute\Groups;

class ProductVariantOption
{
    #[Groups([ProductVariant::NORMALIZATION_GROUP])]
    public string $sequenceNumber;

    #[Groups([ProductVariant::NORMALIZATION_GROUP])]
    public string $productFeature;

    #[Groups([ProductVariant::NORMALIZATION_GROUP])]
    public string $description;

    #[Groups([ProductVariant::NORMALIZATION_GROUP])]
    public string $option;

    #[Groups([ProductVariant::NORMALIZATION_GROUP])]
    public string $optionDescriptionByProductFeature;
}
