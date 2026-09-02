<?php

declare(strict_types=1);

namespace App\DataTable\Filter\Type;

use App\Form\Type\Catalogue\ProductAutocompleteType;
use App\Sdk\Resource\Product;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFilterType extends AbstractApiFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ProductAutocompleteType::class,
                'value_extractor' => static fn (Product $product) => $product->name,
                'active_filter_formatter' => static function (FilterData $filterData) {
                    /** @var Product|array<Product>|null $resource */
                    $resource = $filterData->getValue();

                    if (null === $resource) {
                        return null;
                    }

                    $format = static fn (Product $product): string => $product->name;

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
