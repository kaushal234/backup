<?php

declare(strict_types=1);

namespace App\Form\Type\Airport;

use App\Form\Type\AutocompleteType;
use App\Sdk\Resource\Airport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AirportAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'route_name' => 'autocomplete_airports',
            'resource_class' => Airport::class,
            'placeholder' => 'extranet.placeholder.airport',
            'min_characters' => 2,
            'choice_label' => static function ($choice) {
                if ($choice instanceof Airport) {
                    return \sprintf('%s - %s', $choice->code, $choice->city);
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
