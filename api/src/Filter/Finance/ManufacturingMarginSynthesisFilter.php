<?php

declare(strict_types=1);

namespace App\Filter\Finance;

use ApiPlatform\Doctrine\Common\Filter\DateFilterInterface;
use ApiPlatform\Metadata\FilterInterface;

class ManufacturingMarginSynthesisFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [
            'factory' => ['property' => 'factory', 'type' => 'string', 'required' => false],
        ];

        $description += $this->getFilterDescription('exportDate', DateFilterInterface::PARAMETER_BEFORE);
        $description += $this->getFilterDescription('exportDate', DateFilterInterface::PARAMETER_STRICTLY_BEFORE);
        $description += $this->getFilterDescription('exportDate', DateFilterInterface::PARAMETER_AFTER);

        return $description + $this->getFilterDescription('exportDate', DateFilterInterface::PARAMETER_STRICTLY_AFTER);
    }

    /**
     * Gets filter description.
     */
    protected function getFilterDescription(string $property, string $period): array
    {
        return [
            \sprintf('%s[%s]', $property, $period) => [
                'property' => $property,
                'type' => \DateTimeInterface::class,
                'required' => false,
            ],
        ];
    }
}
