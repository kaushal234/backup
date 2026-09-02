<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders;

use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;
use Doctrine\ORM\EntityManagerInterface;

class TocCustomerServiceRecordEmailBuilder extends AbstractTocEmailBuilder
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return TechnicianOnCallMailSubject::TOC_CSR_CREATED === $subject;
    }

    protected function buildContext(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $technicianOnCall,
        array $context,
    ): array {
        return [
            ...$context,
            'intervention' => $context['customerServiceRecord']->getOpenIntervention(),
            'leader' => \sprintf('%s, %s', $context['customerServiceRecord']->getOpenIntervention()->leader->getLastname(), $context['customerServiceRecord']->getOpenIntervention()->leader->getFirstname()),
        ];
    }

    protected function getAdditionalTranslationParameters(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): array
    {
        return [
            '%openDays%' => $technicianOnCall->getOpenDays(),
            '%sso%' => $technicianOnCall->equipmentRecord?->getSalesOrganisation()->getName() ?? '---',
            '%customer%' => $technicianOnCall->customer->getName(),
            '%indiceFactor%' => $technicianOnCall->indiceFactor,
        ];
    }
}
