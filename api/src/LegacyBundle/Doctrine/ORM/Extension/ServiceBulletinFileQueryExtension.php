<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Entity\ServiceBulletinFile;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Handles business visibility rules for ServiceBulletin files (confidentiality levels).
 */
final class ServiceBulletinFileQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->apply($queryBuilder, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->apply($queryBuilder, $resourceClass);
    }

    private function apply(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (ServiceBulletinFile::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user instanceof People) {
            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%s.level = :level', $rootAlias))
            ->setParameter('level', ServiceBulletinFile::CUSTOMER_LEVEL)
        ;
    }
}
