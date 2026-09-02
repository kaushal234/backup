<?php

declare(strict_types=1);

namespace App\Filter\MIS\Module;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Module\Module;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class OrderByOpenUpdateTasksCountFilter implements FilterInterface
{
    private const ORDER_PARAM = 'order';
    private const KEY = 'countUpdateTasks'; // ?order[countUpdateTasks]=asc|desc

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (Module::class !== $resourceClass) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $order = $request->query->all(self::ORDER_PARAM);
        if (!isset($order[self::KEY])) {
            return;
        }

        $direction = 'asc' === mb_strtolower((string) $order[self::KEY]) ? 'ASC' : 'DESC';

        $alias = $queryBuilder->getRootAliases()[0];

        $updateTaskAlias = $queryNameGenerator->generateJoinAlias('updateTask');

        $countAlias = 'openUpdateTasksCount';

        $queryBuilder
            ->leftJoin(
                UpdateTask::class,
                $updateTaskAlias,
                'WITH',
                \sprintf('%s.thirdPartyApp = %s AND %s.done = false', $updateTaskAlias, $alias, $updateTaskAlias)
            )
            ->addSelect(\sprintf('COUNT(DISTINCT %s.id) AS HIDDEN %s', $updateTaskAlias, $countAlias))
            ->addGroupBy(\sprintf('%s.id', $alias))
            ->addOrderBy($countAlias, $direction);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::ORDER_PARAM.'['.self::KEY.']' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Order by the number of OPEN update tasks (done=false). Values: asc|desc.',
            ],
        ];
    }
}
