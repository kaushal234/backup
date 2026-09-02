<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders;

use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;

class TocSolvedExternalEmailBuilder extends AbstractTocEmailBuilder
{
    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return TechnicianOnCallMailSubject::TOC_SOLVED_EXTERNAL === $subject;
    }

    protected function getAdditionalTranslationParameters(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): array
    {
        return [
            '%serialNumber%' => null !== $technicianOnCall->equipmentRecord
                ? \sprintf('SN# %s', $technicianOnCall->equipmentRecord->getSerialNumber())
                : (!empty($technicianOnCall->serialNumber) ? \sprintf('Customer Asset# %s', $technicianOnCall->serialNumber) : '---'),
        ];
    }
}
