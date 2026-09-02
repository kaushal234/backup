<?php

declare(strict_types=1);

namespace App\Form\Type\Catalogue;

use App\Form\Type\AutocompleteType;
use App\Sdk\Resource\ProductType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductTypeAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'route_name' => 'autocomplete_product_type',
            'resource_class' => ProductType::class,
            'choice_label' => static function ($choice) {
                if ($choice instanceof ProductType) {
                    return $choice->englishName;
                }

                return $choice;
            },
        ]);
    }

    public function getParent(): string
    {
        return AutocompleteType::class;
    }
}
