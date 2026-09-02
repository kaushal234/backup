<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\Form\Type\Catalogue\ProductTypeChoiceType;
use App\Sdk\Resource\ProductType;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTypeFilterType extends AbstractApiFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ProductTypeChoiceType::class,
                'value_extractor' => static fn (ProductType $productType) => $productType->getIri(),
                'active_filter_formatter' => static function (FilterData $filterData) {
                    /** @var ProductType|array<ProductType>|null $resource */
                    $resource = $filterData->getValue();

                    if (null === $resource) {
                        return null;
                    }

                    $format = static fn (ProductType $productType): string => $productType->englishName;

                    if (\is_array($resource)) {
                        if ([] === $resource) {
                            return null;
                        }

                        return implode(', ', array_map($format, $resource));
                    }

                    return $format($resource);
                },
            ])
        ;
    }
}
