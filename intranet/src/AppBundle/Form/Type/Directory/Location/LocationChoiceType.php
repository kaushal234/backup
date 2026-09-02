<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

class LocationChoiceType extends AbstractLocationChoiceType
{
    public function getBlockPrefix(): string
    {
        return 'app_location_choice';
    }
}
