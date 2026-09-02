<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders;

use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;

class TocFactoryFlagEmailBuilder extends AbstractTocEmailBuilder
{
    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return TechnicianOnCallMailSubject::TOC_FACTORY_FLAG === $subject;
    }

    protected function getSubjectTranslationKey(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): string
    {
        return \sprintf(
            'toc.subject.%s_%s',
            $subject->value,
            $technicianOnCall->factoryFlag ? 'open' : 'closed'
        );
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
