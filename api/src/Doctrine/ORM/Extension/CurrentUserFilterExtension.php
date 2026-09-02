<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\DocumentTranslation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class CurrentUserFilterExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
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
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Adds "WHERE <alias>.user = :current_user" for DocumentTranslation.
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (DocumentTranslation::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();

        $aliases = $queryBuilder->getRootAliases();
        if (empty($aliases)) {
            return;
        }
        $alias = $aliases[0];

        if (!$user) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $queryBuilder->andWhere(\sprintf('%s.user = :current_user', $alias))
            ->setParameter('current_user', $user);
    }
}
