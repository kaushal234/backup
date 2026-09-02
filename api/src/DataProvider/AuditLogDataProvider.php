<?php

declare(strict_types=1);

namespace App\DataProvider;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use App\Audible\AudibleProvider;
use App\Manager\Audit\AuditManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class AuditLogDataProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')] private ProviderInterface $provider,
        private AudibleProvider $audibleProvider,
        private RequestStack $requestStack,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private AuditManager $manager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $typeFilter = $this->requestStack->getCurrentRequest()->query->get('auditType');
        if (null === $typeFilter) {
            throw new BadRequestHttpException('Filter Audit Type is mandatory on this route.');
        }

        $propertyFilter = $this->requestStack->getCurrentRequest()->query->get('property');
        if (null === $propertyFilter) {
            throw new BadRequestHttpException('Filter Property is mandatory on this route.');
        }

        $auditLogFilters = ['auditType', 'property', 'referenceId'];
        $resourceFilters = array_diff_key($context['filters'] ?? [], array_flip($auditLogFilters));

        $ids = [];

        if ([] !== $resourceFilters) {
            $audible = $this->audibleProvider->getAudibleConfiguration($typeFilter, $propertyFilter);

            $subjectOperation = $this->resourceMetadataFactory->create($audible->getClass())
                ->getOperation(forceCollection: true)
                ->withPaginationEnabled(false)
                ->withForceEager(false)
            ;

            /** @var Paginator $subjects */
            $subjects = $this->provider->provide($subjectOperation, [], [...$context, 'filters' => $resourceFilters]);

            foreach ($subjects as $subject) {
                $ids[] = $subject->getId();
            }
        }

        return match ($operation->getName()) {
            'audit_logs_by_month' => $this->manager->getAuditLogsFilteredByMonth($ids, $typeFilter, $propertyFilter),
            'audit_logs_time' => $this->manager->getAuditLogsFilteredTime($ids, $typeFilter, $propertyFilter),
            default => $this->manager->getAuditLogsFiltered($ids, $typeFilter, $propertyFilter),
        };
    }
}
