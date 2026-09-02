<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Sdk\Resource\Country;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'route_name' => 'autocomplete_countries',
            'resource_class' => Country::class,
            'choice_label' => static function ($choice) {
                if ($choice instanceof Country) {
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
