<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\ApiPlatform\UniqueResourceMetadataCollectionFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

class DiscriminatorFilter extends AbstractFilter
{
    /**
     * @var string
     */
    final public const FILTER_RESOURCE_TYPE_PROPERTY = 'resourceType';

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly UniqueResourceMetadataCollectionFactory $resourceMetadataFactory,
        ?LoggerInterface $logger = null,
        ?array $properties = null
    ) {
        parent::__construct($managerRegistry, $logger, $properties);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            static::FILTER_RESOURCE_TYPE_PROPERTY => [
                'property' => static::FILTER_RESOURCE_TYPE_PROPERTY,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ($property !== static::FILTER_RESOURCE_TYPE_PROPERTY) {
            return;
        }

        /** @var ObjectManager $manager */
        $manager = $this->managerRegistry->getManagerForClass($resourceClass);
        $classMetadataFactory = $manager->getMetadataFactory();

        /** @var ClassMetadata $classMetadata */
        $classMetadata = $classMetadataFactory->getMetadataFor($resourceClass);
        foreach ($classMetadata->discriminatorMap as $discriminator => $entityClass) {
            $apiResource = $this->resourceMetadataFactory->getApiResource($entityClass);

            if ($apiResource->getShortName() === $value) {
                $alias = $queryBuilder->getAllAliases()[0];
                $queryBuilder->andWhere($alias.' INSTANCE OF :instance');
                $queryBuilder->setParameter('instance', $discriminator);

                return;
            }
        }

        $queryBuilder->where('1=0');
    }
}
