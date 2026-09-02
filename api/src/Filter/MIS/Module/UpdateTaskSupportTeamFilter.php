<?php

declare(strict_types=1);

namespace App\Filter\MIS\Module;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use App\Util\IriToId;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UpdateTaskSupportTeamFilter implements FilterInterface
{
    final public const FILTER_USED_PROPERTY = 'update_task_supportTeam';

    public function __construct(
        protected RequestStack $requestStack,
        private IriToId $iriToId,
    ) {
    }

    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $raw = $request->query->all()[static::FILTER_USED_PROPERTY]
            ?? $request->query->get(static::FILTER_USED_PROPERTY);

        if (empty($raw)) {
            return;
        }

        $values = \is_array($raw) ? $raw : [$raw];

        if (UpdateTask::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the UpdateTask resource');
        }

        $ids = array_map(
            fn (string $value) => is_numeric($value) ? $value : $this->iriToId->getId($value),
            $values
        );

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->innerJoin(
                \App\Entity\Directory\People::class,
                'filter_people',
                'WITH',
                "filter_people.id = {$rootAlias}.user"
            )
            ->innerJoin('filter_people.premise', 'filter_premise')
            ->innerJoin('filter_premise.supportTeam', 'filter_support_team')
            ->andWhere('filter_support_team.id IN (:supportTeamIds)')
            ->setParameter('supportTeamIds', $ids);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_USED_PROPERTY => [
                'property' => static::FILTER_USED_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
