<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop;

trait SignalCodeDescriptionTrait
{
    /** Could be changed to const with PHP 8.2 (not available before) */
    private array $signalCodeDescription = [
        'CH0' => 'Chapter 0',
        'CH1' => 'Chapter 1',
        'CH2' => 'Chapter 2',
        'CH3' => 'Chapter 3',
        'CH4' => 'Chapter 4',
        'CH5' => 'Chapter 5',
        'A' => 'Body-Chassis',
        'B' => 'Covers and Panels',
        'C' => 'Boom',
        'D' => 'Bridge',
        'E' => 'Elevator',
        'F' => 'Lifting-Scissors System',
        'G' => 'Power Plant',
        'H' => 'Hydraulic System',
        'I' => 'Pneumatic System',
        'J' => 'Refrigeration System',
        'K' => 'Electrical System',
        'L' => 'Suspension, Tires and Brakes',
        'M' => 'User Interfaces and Cab',
        'N' => 'Accessories and Options',
    ];

    public function getSignalCodeDescription($value): ?string
    {
        if (0 === mb_strpos((string) $value, 'CH')) {
            return $this->signalCodeDescription[$value] ?? null;
        }

        return $this->signalCodeDescription[mb_substr((string) $value, 2, 1)] ?? null;
    }
}
