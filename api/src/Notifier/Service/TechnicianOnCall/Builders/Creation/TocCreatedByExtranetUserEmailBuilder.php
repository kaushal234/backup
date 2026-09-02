<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders\Creation;

use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\Builders\AbstractTocEmailBuilder;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;

class TocCreatedByExtranetUserEmailBuilder extends AbstractTocEmailBuilder
{
    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return TechnicianOnCallMailSubject::TOC_CREATED_BY_CUSTOMER === $subject;
    }

    protected function getAdditionalTranslationParameters(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): array
    {
        return [
            '%customer%' => $technicianOnCall->customer->getName(),
            '%serialNumber%' => null !== $technicianOnCall->equipmentRecord
                ? \sprintf('SN# %s', $technicianOnCall->equipmentRecord->getSerialNumber())
                : (!empty($technicianOnCall->serialNumber) ? \sprintf('Customer Asset# %s', $technicianOnCall->serialNumber) : '---'),
        ];
    }
}
