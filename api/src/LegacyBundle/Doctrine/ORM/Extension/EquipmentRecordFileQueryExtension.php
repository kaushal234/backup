<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Entity\EquipmentRecord;
use LegacyBundle\Entity\EquipmentRecordFile;
use LegacyBundle\Security\ExtranetUserCustomerResolver;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Restricts equipment customer files to the equipment records an extranet user is allowed to see,
 * mirroring the equipment view access rules: the user must be buyer, maintainer or end user
 * (through their ACLs) of the equipment the file is attached to. Internal People users are not restricted.
 */
final class EquipmentRecordFileQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly ExtranetUserCustomerResolver $customerResolver,
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    private function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass): void
    {
        if (EquipmentRecordFile::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user instanceof People) {
            return;
        }

        if ($user instanceof ExtranetUser) {
            $customerLegacyIds = $this->customerResolver->getAccessibleCustomerLegacyIds($user);

            // Fail-safe: if no associated customers are found, block access.
            if (empty($customerLegacyIds)) {
                $queryBuilder->andWhere('1 = 0');

                return;
            }

            $erAlias = $queryNameGenerator->generateJoinAlias('er');
            $queryBuilder
                ->innerJoin(EquipmentRecord::class, $erAlias, 'WITH', \sprintf('%s.parentId = %s.id', $rootAlias, $erAlias))
                ->andWhere($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->in(\sprintf('%s.buyer', $erAlias), ':customerLegacyIds'),
                    $queryBuilder->expr()->in(\sprintf('%s.maintainer', $erAlias), ':customerLegacyIds'),
                    $queryBuilder->expr()->in(\sprintf('%s.endUser', $erAlias), ':customerLegacyIds'),
                ))
                ->setParameter('customerLegacyIds', $customerLegacyIds)
            ;

            return;
        }

        // Global fail-safe: block access for any other unhandled user type.
        $queryBuilder->andWhere('1 = 0');
    }
}
