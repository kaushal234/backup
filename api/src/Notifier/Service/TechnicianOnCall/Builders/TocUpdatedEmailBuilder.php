<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall\Builders;

use App\Entity\Service\TechnicianOnCall;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;

class TocUpdatedEmailBuilder extends AbstractTocEmailBuilder
{
    /**
     * symptoms/rootCause/solution get their own "documentation" boxes in the email
     * instead of the generic "Field X changed from Y to Z" line. Their originalX
     * counterparts (translation-tracking duplicates) are never shown to the reader.
     */
    private const array DOCUMENTATION_FIELDS = [
        'symptoms' => 'originalSymptoms',
        'rootCause' => 'originalRootCause',
        'solution' => 'originalSolution',
    ];

    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return TechnicianOnCallMailSubject::TOC_UPDATED === $subject;
    }

    protected function getAdditionalTranslationParameters(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall): array
    {
        return [
            '%openDays%' => $technicianOnCall->getOpenDays(),
            '%sso%' => $technicianOnCall->equipmentRecord?->getSalesOrganisation()->getName() ?? '---',
            '%customer%' => $technicianOnCall->customer->getName(),
        ];
    }

    protected function buildContext(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $technicianOnCall,
        array $context,
    ): array {
        $context = parent::buildContext($subject, $technicianOnCall, $context);

        $changeSet = $context['changeSet'] ?? [];
        $documentationFields = [];

        foreach (self::DOCUMENTATION_FIELDS as $field => $originalField) {
            if (\array_key_exists($field, $changeSet) && !empty($changeSet[$field][1])) {
                $documentationFields[] = $field;
            }
            unset($changeSet[$field], $changeSet[$originalField]);
        }

        $context['changeSet'] = $changeSet;
        $context['documentationFields'] = $documentationFields;

        return $context;
    }
}
