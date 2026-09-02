<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Common\SubscriptionResourceModule;
use App\Entity\Module\Module;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SubscriptionModuleFilter implements FilterInterface
{
    /**
     * @var string
     */
    private const FILTER_PROPERTY = 'module';

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request || !$request->query->has(self::FILTER_PROPERTY)) {
            return;
        }

        $rawValues = $request->query->all()[self::FILTER_PROPERTY] ?? null;
        $iris = \is_array($rawValues) ? $rawValues : [$rawValues];

        $prefixes = [];
        foreach ($iris as $iri) {
            if (!\is_string($iri) || '' === $iri) {
                continue;
            }

            try {
                $module = $this->iriConverter->getResourceFromIri($iri);
            } catch (\Exception) {
                continue;
            }

            if (!$module instanceof Module) {
                continue;
            }

            $enum = SubscriptionResourceModule::tryFrom($module->getName());
            if (null === $enum) {
                continue;
            }

            foreach ($enum->iriPrefixes() as $prefix) {
                $prefixes[] = $prefix;
            }
        }

        if (empty($prefixes)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $orX = $queryBuilder->expr()->orX();

        foreach ($prefixes as $prefix) {
            $parameter = $queryNameGenerator->generateParameterName(self::FILTER_PROPERTY);
            $orX->add($queryBuilder->expr()->like(\sprintf('%s.resource', $rootAlias), \sprintf(':%s', $parameter)));
            $queryBuilder->setParameter($parameter, $prefix.'%');
        }

        $queryBuilder->andWhere($orX);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_PROPERTY.'[]' => [
                'property' => self::FILTER_PROPERTY,
                'type' => 'string',
                'required' => false,
                'is_collection' => true,
            ],
        ];
    }
}
