<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders;

use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;

class TocPendingToInProgressEmailBuilder extends AbstractTocEmailBuilder
{
    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return \in_array($subject, [
            TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_INTERNAL,
            TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_EXTERNAL,
        ], true);
    }

    protected function getAdditionalTranslationParameters(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): array
    {
        return [
            '%openDays%' => $technicianOnCall->getOpenDays(),
            '%serialNumber%' => null !== $technicianOnCall->equipmentRecord
                ? \sprintf('SN# %s', $technicianOnCall->equipmentRecord->getSerialNumber())
                : (!empty($technicianOnCall->serialNumber) ? \sprintf('Customer Asset# %s', $technicianOnCall->serialNumber) : '---'),
        ];
    }
}
