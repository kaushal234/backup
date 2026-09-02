<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

class GenderHelper
{
    public function getGenderFromProperty(?string $property)
    {
        $genderMale = ['Mr.', 'M', 'M.', 'Mr'];
        $genderFemale = ['Miss', 'Mrs.', 'Ms', 'Ms.', 'Mme', 'Miss.', 'Mrs'];

        if (\in_array($property, $genderMale, true)) {
            $property = 'Mr';
        } elseif (\in_array($property, $genderFemale, true)) {
            $property = 'Mrs';
        } else {
            $property = null;
        }

        return $property;
    }
}
