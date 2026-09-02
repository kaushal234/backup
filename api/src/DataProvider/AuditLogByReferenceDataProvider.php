<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Manager\Audit\AuditManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class AuditLogByReferenceDataProvider implements ProviderInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private AuditManager $manager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $typeFilter = $this->requestStack->getCurrentRequest()->query->get('auditType');
        $propertyFilter = $this->requestStack->getCurrentRequest()->query->get('property');
        $referenceIdFilter = $this->requestStack->getCurrentRequest()->query->get('referenceId');

        if (!$referenceIdFilter) {
            throw new BadRequestHttpException('Filter Reference ID is mandatory on this route.');
        }

        $ids = [(int) $referenceIdFilter];

        return match ($operation->getName()) {
            'audit_logs_by_reference' => $this->manager->getAuditLogsFilteredByReference($ids, $typeFilter, $propertyFilter),
            'audit_logs_time_by_reference' => $this->manager->getAuditLogsFilteredTime($ids, $typeFilter, $propertyFilter),
            default => $this->manager->getAuditLogsFiltered($ids, $typeFilter, $propertyFilter),
        };
    }
}
