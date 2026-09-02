<?php

declare(strict_types=1);

namespace App\Filter\Service;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Util\IriToId;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerServiceRecordPlanningFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'technicianPlanned';

    public function __construct(
        protected RequestStack $requestStack,
        private IriToId $iriToId,
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

        $values = $request->query->all(static::FILTER_USED_PROPERTY);

        if (empty($values)) {
            return;
        }

        if (AbstractCustomerServiceRecord::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Customer Service Record resource');
        }

        $queryBuilder
            ->innerJoin('o.interventions', 'intervention')
            ->leftJoin('intervention.operators', 'operator')
            ->leftJoin('intervention.leader', 'leader')
        ;

        $orStatements = $queryBuilder->expr()->orX();

        $technicians = [];
        foreach ($values as $value) {
            $technicians[] = is_numeric($value) ? $value : $this->iriToId->getId($value);
        }
        $orStatements->add($queryBuilder->expr()->in('operator', ':technicians'));
        $orStatements->add($queryBuilder->expr()->in('leader', ':technicians'));
        $queryBuilder->setParameter('technicians', $technicians);

        $queryBuilder->andWhere($orStatements);
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
            ],
        ];
    }
}
