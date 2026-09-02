<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use ApiBundle\Model\ApiData;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomerServiceRecordSPRExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('csr_spr', [$this, 'getSpr']),
            new TwigFilter('sprs_open', [$this, 'getOpens']),
        ];
    }

    public function getSpr($customerServiceRecord): ?array
    {
        if ($customerServiceRecord instanceof ApiData) {
            $customerServiceRecord = $customerServiceRecord->toArray();
        }

        if ('sb' === $customerServiceRecord['type'] && isset($customerServiceRecord['serviceBulletin'])) {
            return $customerServiceRecord['serviceBulletin']['sparePartsRequest'];
        }

        if ('toc' === $customerServiceRecord['type'] && isset($customerServiceRecord['toc'])) {
            return $customerServiceRecord['toc']['sparePartsRequest'];
        }

        return null;
    }

    public function getOpens(?array $sparePartsRequests): array|bool|null
    {
        if (null === $sparePartsRequests || [] === $sparePartsRequests) {
            return null;
        }

        foreach ($sparePartsRequests as $sparePartsRequest) {
            if (\in_array($sparePartsRequest['status'], ['PENDING', 'OPEN'], true)) {
                return $sparePartsRequests;
            }
        }

        return false;
    }
}
