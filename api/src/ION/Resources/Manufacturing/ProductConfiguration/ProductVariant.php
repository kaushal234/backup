<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\ProductConfiguration;

use Symfony\Component\Serializer\Attribute\Groups;

class ProductVariant
{
    final public const NORMALIZATION_GROUP = 'variant';

    #[Groups([self::NORMALIZATION_GROUP])]
    public string $productVariant;

    #[Groups([self::NORMALIZATION_GROUP])]
    public string $description;

    #[Groups([self::NORMALIZATION_GROUP])]
    public string $item;

    #[Groups([self::NORMALIZATION_GROUP])]
    public string $referenceOrder;
    /**
     * @var ProductVariantOption[]
     */
    #[Groups([self::NORMALIZATION_GROUP])]
    private array $options = [];

    public function getOptions(): array
    {
        return $this->options;
    }

    public function addOption(ProductVariantOption $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    public function removeOption(ProductVariantOption $option): self
    {
        return $this;
    }
}
