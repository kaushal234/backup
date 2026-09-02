<?php

declare(strict_types=1);

namespace App\Filter\Service;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Service\TechnicianOnCall;
use App\Util\Iri;
use Doctrine\ORM\QueryBuilder;

class TechnicianOnCallActorFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'actor';

    /**
     * {@inheritdoc}
     */
    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (TechnicianOnCall::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the TechnicianOnCall resource');
        }

        $filters = $context['filters'] ?? [];
        $values = $filters['actor'] ?? null;

        if (null === $values) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $orX = $queryBuilder->expr()->orX();

        foreach ($values as $iri) {
            $id = Iri::id($iri);
            $assignee = $queryNameGenerator->generateParameterName('assignee');
            $technician = $queryNameGenerator->generateParameterName('technician');

            $orX->add(\sprintf('%s.assignee = :%s', $alias, $assignee));
            $orX->add(\sprintf('%s.technician = :%s', $alias, $technician));

            $queryBuilder
                ->setParameter($assignee, $id)
                ->setParameter($technician, $id)
            ;
        }

        $queryBuilder->andWhere($orX);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_USED_PROPERTY => [
                'property' => static::FILTER_USED_PROPERTY,
                'type' => 'array',
                'required' => false,
                'description' => 'Filter by the actor of the TechnicianOnCall (assignee or technician)',
            ],
        ];
    }
}
