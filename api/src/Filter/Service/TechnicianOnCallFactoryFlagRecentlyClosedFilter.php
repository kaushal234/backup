<?php

declare(strict_types=1);

namespace App\Filter\Service;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\AuditLog;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TechnicianOnCallFactoryFlagRecentlyClosedFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'factoryFlagRecentlyClosed';

    public function __construct(
        protected RequestStack $requestStack,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $recentlyClosed = $request->query->get(static::FILTER_USED_PROPERTY);

        if (!$recentlyClosed || 'false' === $recentlyClosed) {
            return;
        }

        if (TechnicianOnCall::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the TechnicianOnCall resource');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $subQBAliasTrue = 'alTrue';
        $subQBAliasFalse = 'alFalse';

        $subQB = $queryBuilder->getEntityManager()->createQueryBuilder();
        $subQB->select('1')
            ->from(AuditLog::class, $subQBAliasTrue)
            ->innerJoin(
                AuditLog::class,
                $subQBAliasFalse,
                'WITH',
                "$subQBAliasFalse.id = $subQBAliasTrue.next"
            )
            ->where("$subQBAliasTrue.referenceId = $rootAlias.id")
            ->andWhere("$subQBAliasTrue.auditType = :type")
            ->andWhere("$subQBAliasTrue.property = :property")
            ->andWhere("$subQBAliasTrue.value = :trueValue")
            ->andWhere("$subQBAliasFalse.auditType = :type")
            ->andWhere("$subQBAliasFalse.property = :property")
            ->andWhere("$subQBAliasFalse.createdAt >= :recentDate")
            ->andWhere($subQB->expr()->orX(
                "$subQBAliasFalse.value IS NULL",
                "$subQBAliasFalse.value = :empty",
                "$subQBAliasFalse.value = :zero"
            ))
            ->andWhere("$rootAlias.factoryFlag = 0")
        ;

        $queryBuilder->andWhere($queryBuilder->expr()->exists($subQB->getDQL()))
            ->setParameter('type', 'technician_on_call')
            ->setParameter('property', 'factoryFlag')
            ->setParameter('trueValue', '1')
            ->setParameter('recentDate', new \DateTimeImmutable('-4 days'))
            ->setParameter('empty', '')
            ->setParameter('zero', '0');
    }

    /**
     * {@inheritdoc}
     */
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
