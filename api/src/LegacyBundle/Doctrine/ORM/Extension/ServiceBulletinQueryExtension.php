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
use LegacyBundle\Entity\ServiceBulletin;
use LegacyBundle\Entity\ServiceBulletinLine;
use LegacyBundle\Security\ExtranetUserCustomerResolver;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Applies global security restrictions to ServiceBulletins and related resources (like files).
 * Restrictions are based on Equipment ownership/usage linked to the bulletin.
 */
final class ServiceBulletinQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /**
     * @var list<string>
     */
    private const VISIBLE_STATUSES = ['PARTIAL_IMPLEMENTATION', 'IMPLEMENTATION', 'CLOSED'];

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
        $reflectionClass = new \ReflectionClass($resourceClass);
        $attributes = $reflectionClass->getAttributes(ServiceBulletinSecurityAware::class);

        if (empty($attributes)) {
            return;
        }

        /** @var ServiceBulletinSecurityAware $securityAware */
        $securityAware = $attributes[0]->newInstance();

        $user = $this->security->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user instanceof People) {
            return;
        }

        if ($user instanceof ExtranetUser) {
            $sbAlias = $rootAlias;

            // If the resource is a child (like ServiceBulletinFile),
            // we join the parent ServiceBulletin to apply its security rules.
            if (null !== $securityAware->parentIdProperty) {
                $sbAlias = $queryNameGenerator->generateJoinAlias('sb');
                $queryBuilder->innerJoin(ServiceBulletin::class, $sbAlias, 'WITH', \sprintf('%s.%s = %s.id', $rootAlias, $securityAware->parentIdProperty, $sbAlias));
            }

            $customerLegacyIds = $this->customerResolver->getAccessibleCustomerLegacyIds($user);

            // Fail-safe: if no associated customers are found, block access
            if (empty($customerLegacyIds)) {
                $queryBuilder->andWhere('1 = 0');

                return;
            }

            // PERFORMANCE & INTEGRITY: Use an EXISTS subquery to check if the user has access
            // to at least one EquipmentRecord linked to the Bulletin (as Buyer, Maintainer, or End User).
            // This avoids duplicates in collection results that a simple innerJoin might cause.
            $subQueryLinesAlias = $queryNameGenerator->generateJoinAlias('sbl_sub');
            $subQueryErAlias = $queryNameGenerator->generateJoinAlias('er_sub');

            $subQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from(ServiceBulletinLine::class, $subQueryLinesAlias)
                ->innerJoin(\sprintf('%s.equipmentRecord', $subQueryLinesAlias), $subQueryErAlias)
                ->where(\sprintf('%s.serviceBulletin = %s', $subQueryLinesAlias, $sbAlias))
                // Only expose bulletins created after the equipment's green tag date.
                ->andWhere(\sprintf('%s.createdAt > %s.greenTagDate', $sbAlias, $subQueryErAlias))
                ->andWhere($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->in(\sprintf('%s.buyer', $subQueryErAlias), ':customerLegacyIds'),
                    $queryBuilder->expr()->in(\sprintf('%s.maintainer', $subQueryErAlias), ':customerLegacyIds'),
                    $queryBuilder->expr()->in(\sprintf('%s.endUser', $subQueryErAlias), ':customerLegacyIds'),
                ))
                ->getDQL()
            ;

            $queryBuilder
                ->andWhere($queryBuilder->expr()->exists($subQuery))
                // Hide confidential bulletins and restrict to publicly visible statuses.
                ->andWhere(\sprintf('%s.confidential = :sbConfidential', $sbAlias))
                ->andWhere(\sprintf('%s.status IN (:sbVisibleStatuses)', $sbAlias))
                ->setParameter('customerLegacyIds', $customerLegacyIds)
                ->setParameter('sbConfidential', 'N')
                ->setParameter('sbVisibleStatuses', self::VISIBLE_STATUSES)
            ;

            // Kit parts availability (TTS-1062, TTS-56529): only show a bulletin once at least one
            // of its lines (any customer's, not necessarily this one) has reached a status
            // confirming TLD has the parts ready, otherwise customers see it before it's actionable.
            $readyLineAlias = $queryNameGenerator->generateJoinAlias('sbl_ready');
            $readyLineSubQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
                ->select('1')
                ->from(ServiceBulletinLine::class, $readyLineAlias)
                ->where(\sprintf('%s.serviceBulletin = %s', $readyLineAlias, $sbAlias))
                ->andWhere(\sprintf('%s.status IN (:visibleLineStatuses)', $readyLineAlias))
                ->getDQL()
            ;

            $queryBuilder
                ->andWhere($queryBuilder->expr()->exists($readyLineSubQuery))
                ->setParameter('visibleLineStatuses', ServiceBulletinLine::VISIBLE_ON_EXTRANET_STATUSES)
            ;

            return;
        }

        // Global Fail-safe: block access for any other unhandled user type
        $queryBuilder->andWhere('1 = 0');
    }
}
