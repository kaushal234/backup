<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders\Creation;

use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\Builders\AbstractTocEmailBuilder;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;

class TocCreatedOnBlackCatEmailBuilder extends AbstractTocEmailBuilder
{
    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return TechnicianOnCallMailSubject::TOC_BLACK_CAT === $subject;
    }

    protected function getAdditionalTranslationParameters(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): array
    {
        return [
            '%openDays%' => $technicianOnCall->getOpenDays(),
            '%sso%' => $technicianOnCall->equipmentRecord?->getSalesOrganisation()->getName() ?? '---',
            '%customer%' => $technicianOnCall->customer->getName(),
        ];
    }
}
