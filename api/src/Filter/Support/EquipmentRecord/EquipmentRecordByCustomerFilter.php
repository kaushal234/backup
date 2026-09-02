<?php

declare(strict_types=1);

namespace App\Filter\Support\EquipmentRecord;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class EquipmentRecordByCustomerFilter extends AbstractFilter
{
    private const BY_CUSTOMER = 'by_customer';

    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        ManagerRegistry $managerRegistry,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            self::BY_CUSTOMER => [
                'property' => 'buyer or endUser or maintainer',
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Search by buyer or end user or maintainer',
                    'name' => 'Buyer or EndUser or Maintainer Name',
                    'type' => 'Search',
                ],
            ],
        ];
    }

    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (!$value || !$this->isPropertyEnabled($property, $resourceClass) || self::BY_CUSTOMER !== $property) {
            return;
        }

        $customer = $this->iriConverter->getResourceFromIri($value);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $orX = new Orx();
        $orX
            ->add(\sprintf('%s.endUser = :customer', $rootAlias))
            ->add(\sprintf('%s.buyer = :customer', $rootAlias))
            ->add(\sprintf('%s.maintainer = :customer', $rootAlias))
        ;

        $queryBuilder
            ->andWhere($orX)
            ->setParameter('customer', $customer);
    }
}
