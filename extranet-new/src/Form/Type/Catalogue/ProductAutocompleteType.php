<?php

declare(strict_types=1);

namespace App\Form\Type\Catalogue;

use App\Form\Type\AutocompleteType;
use App\Sdk\Resource\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'route_name' => 'autocomplete_product',
            'resource_class' => Product::class,
            'choice_label' => static function ($choice) {
                if ($choice instanceof Product) {
                    return $choice->name;
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
