<?php

declare(strict_types=1);

namespace LegacyBundle\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Entity\LegacySupplierEntityInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @todo Delete for ln-compatibility. Don't forget to delete ApiFilter on the concerned entities
 */
class SupplierFilter implements FilterInterface
{
    /**
     * @var string
     */
    private const SUPPLIER_PROPERTY = 'supplier';

    private readonly RequestStack $requestStack;

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

        if (!$request->query->has(self::SUPPLIER_PROPERTY)) {
            return;
        }

        $class = new \ReflectionClass($resourceClass);
        if (!$class->implementsInterface(LegacySupplierEntityInterface::class)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $conditions = [];
        foreach ((array) $request->query->get(self::SUPPLIER_PROPERTY) as $supplier) {
            if (false === mb_strpos($supplier, '@')) {
                throw new InvalidArgumentException('Each supplier must contain a @ symbol to separate supplier number and ERP');
            }
            [$supplierNumber, $supplierErp] = explode('@', $supplier);
            $supplierNumberParameter = $queryNameGenerator->generateParameterName(self::SUPPLIER_PROPERTY.'_number');
            $supplierErpParameter = $queryNameGenerator->generateParameterName(self::SUPPLIER_PROPERTY.'_erp');
            $conditions[] = \sprintf('(%1$s.supplierNumber = :%2$s AND %1$s.supplierErp = :%3$s)', $rootAlias, $supplierNumberParameter, $supplierErpParameter);
            $queryBuilder->setParameter($supplierNumberParameter, $supplierNumber);
            $queryBuilder->setParameter($supplierErpParameter, $supplierErp);
        }

        $queryBuilder->andWhere(implode(' OR ', $conditions));
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];
        foreach ([self::SUPPLIER_PROPERTY, self::SUPPLIER_PROPERTY.'[]'] as $parameterName) {
            $description[$parameterName] = [
                'property' => self::SUPPLIER_PROPERTY,
                'type' => 'string',
                'required' => false,
            ];
        }

        return $description;
    }
}
