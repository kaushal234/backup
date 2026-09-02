<?php

declare(strict_types=1);

namespace App\Filter\Customer;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerWithCustomerRelationshipTeamFilter implements FilterInterface
{
    /**
     * @var string
     */
    final public const FILTER_USED_PROPERTY = 'has_crt';

    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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

        if (!$request->query->has(static::FILTER_USED_PROPERTY)) {
            return;
        }

        $value = $request->query->get(static::FILTER_USED_PROPERTY);

        if (Customer::class !== $resourceClass) {
            throw new \Exception('This filter is restricted to the Customer resource');
        }

        if (\in_array($value, [true, 'true', '1'], true)) {
            $queryBuilder
                ->leftJoin(CustomerRelationshipTeam::class, 'crt', Join::WITH, 'o.id = crt.customer')
                ->andWhere($queryBuilder->expr()->isNotNull('crt.id'));
        }

        if (\in_array($value, [false, 'false', '0'], true)) {
            $queryBuilder
                ->leftJoin(CustomerRelationshipTeam::class, 'crt', Join::WITH, 'o.id = crt.customer')
                ->andWhere($queryBuilder->expr()->isNull('crt.id'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_USED_PROPERTY => [
                'property' => static::FILTER_USED_PROPERTY,
                'type' => 'bool',
                'required' => false,
            ],
        ];
    }
}
