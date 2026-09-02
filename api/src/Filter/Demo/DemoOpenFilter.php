<?php

declare(strict_types=1);

namespace App\Filter\Demo;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\Demo;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DemoOpenFilter implements FilterInterface
{
    final public const FILTER_OPEN_PROPERTY = 'open';

    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        if (!$request->query->has(static::FILTER_OPEN_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_OPEN_PROPERTY);

        if (Demo::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Demo resource');
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            return;
        }

        if (\in_array($value, [true, 'true', '1'], true)) {
            $orStatements = $queryBuilder->expr()->orX();

            $orStatements->add($queryBuilder->expr()->in('o.status', ':open_statuses'));
            $orStatements->add($queryBuilder->expr()->gt('o.closingDate', ':two_months_ago'));

            $queryBuilder->andWhere($orStatements);

            $queryBuilder->setParameter('open_statuses', Demo::OPEN_STATUSES);
            $queryBuilder->setParameter('two_months_ago', new \DateTime('2 month ago'));
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_OPEN_PROPERTY => [
                'property' => static::FILTER_OPEN_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
