<?php

declare(strict_types=1);

namespace App\Filter\MIS\Module;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Module\Module;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ModuleWithOpenUpdateTasksOnlyFilter implements FilterInterface
{
    final public const FILTER_PROPERTY = 'withOpenUpdateTasksOnly';

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

        if (!$request->query->has(self::FILTER_PROPERTY)) {
            return;
        }

        $value = $request->query->get(self::FILTER_PROPERTY);

        if (null === $value || '' === $value) {
            return;
        }

        if (!\in_array($value, [true, 'true', '1', 1], true)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->andWhere(\sprintf('%s INSTANCE OF %s', $rootAlias, Extended::class));

        $subAlias = $queryNameGenerator->generateJoinAlias('ut');
        $subQb = $queryBuilder->getEntityManager()->createQueryBuilder();

        $subQb
            ->select('1')
            ->from(UpdateTask::class, $subAlias)
            ->where(\sprintf('%s.thirdPartyApp = %s', $subAlias, $rootAlias))
            ->andWhere(\sprintf('%s.done = false', $subAlias));

        $queryBuilder->andWhere(\sprintf('EXISTS (%s)', $subQb->getDQL()));
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::FILTER_PROPERTY => [
                'property' => self::FILTER_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
