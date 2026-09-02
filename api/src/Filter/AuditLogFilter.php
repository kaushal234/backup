<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Audible\AudibleProvider;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class AuditLogFilter implements FilterInterface
{
    public function __construct(
        private AudibleProvider $audibleProvider,
        private RequestStack $requestStack,
        private ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        #[Autowire(service: 'api_platform.filter_locator')] private ContainerInterface $filterLocator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        $typeFilter = $this->requestStack->getCurrentRequest()->query->get('auditType');
        if (null === $typeFilter) {
            throw new BadRequestHttpException('Filter Audit Type is mandatory on this route.');
        }

        $propertyFilter = $this->requestStack->getCurrentRequest()->query->get('property');
        if (null === $propertyFilter) {
            throw new BadRequestHttpException('Filter Property is mandatory on this route.');
        }

        $audible = $this->audibleProvider->getAudibleConfiguration($typeFilter, $propertyFilter);
        $filters = $this->resourceMetadataFactory->create($class = $audible->getClass())->getOperation(forceCollection: true)->getFilters();

        foreach ($filters as $filterDefinition) {
            /** @var FilterInterface $filter */
            $filter = $this->filterLocator->get($filterDefinition);
            $description += $filter->getDescription($class);
        }

        return $description;
    }
}
