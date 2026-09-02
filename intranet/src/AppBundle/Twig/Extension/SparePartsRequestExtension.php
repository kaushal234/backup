<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SparePartsRequestExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('are_recorded', $this->areRecorded(...)),
        ];
    }

    public function areRecorded(array $sparePartsRequests): ?bool
    {
        foreach ($sparePartsRequests as $sparePartsRequest) {
            if (empty($sparePartsRequest['salesOrder'])) {
                return false;
            }
        }

        return true;
    }
}
