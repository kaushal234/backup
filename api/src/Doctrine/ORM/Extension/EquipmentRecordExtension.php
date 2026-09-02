<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\EquipmentRecord;
use App\Entity\EquipmentRecordStatus;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class EquipmentRecordExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (EquipmentRecord::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        if (!$user instanceof ExtranetUser) {
            return;
        }

        if ('get_equipment_collection' === $operation->getName()) {
            $this->addExtranetUserFilter($queryBuilder, $queryNameGenerator, $user);
            $this->addVisibleToExtranetFilter($queryBuilder);

            return;
        }

        if ('get_by_enduser' === $operation->getName()) {
            $this->addExtranetUserFilterOnEndUser($queryBuilder, $queryNameGenerator, $user);
            $this->addVisibleToExtranetFilter($queryBuilder);
        }
    }

    public function addExtranetUserFilterOnEndUser(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        ExtranetUser $extranetUser,
    ) {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $endUserAlias = $queryNameGenerator->generateJoinAlias('endUser');
        $crtAlias = $queryNameGenerator->generateJoinAlias('crt');
        $aclsAlias = $queryNameGenerator->generateJoinAlias('acls');
        $queryBuilder
            ->innerJoin(\sprintf('%s.endUser', $rootAlias), $endUserAlias)
            ->innerJoin(\sprintf('%s.crt', $endUserAlias), $crtAlias)
            ->innerJoin(\sprintf('%s.acls', $crtAlias), $aclsAlias)
            ->andWhere(\sprintf('%s.extranetUser = :current_extranet_user', $aclsAlias))
            ->setParameter('current_extranet_user', $extranetUser)
        ;
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    private function addExtranetUserFilter(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        ExtranetUser $extranetUser,
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $buyerAlias = $queryNameGenerator->generateJoinAlias('buyer');
        $maintainerAlias = $queryNameGenerator->generateJoinAlias('maintainer');
        $endUserAlias = $queryNameGenerator->generateJoinAlias('endUser');

        $subQueryBuyer = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'extranetUserAclBuyer')
            ->innerJoin('extranetUserAclBuyer.crt', 'crtBuyer')
            ->innerJoin('crtBuyer.customer', 'buyer')
            ->where('extranetUserAclBuyer.extranetUser = :current_extranet_user')
            ->andWhere(\sprintf('buyer = %s', $buyerAlias))
            ->getDQL();

        $subQueryMaintainer = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'extranetUserAclMaintainer')
            ->innerJoin('extranetUserAclMaintainer.crt', 'crtMaintainer')
            ->innerJoin('crtMaintainer.customer', 'maintainer')
            ->where('extranetUserAclMaintainer.extranetUser = :current_extranet_user')
            ->andWhere(\sprintf('maintainer = %s', $maintainerAlias))
            ->getDQL();

        $subQueryEndUser = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'extranetUserAclEndUser')
            ->innerJoin('extranetUserAclEndUser.crt', 'crtEndUser')
            ->innerJoin('crtEndUser.customer', 'endUser')
            ->where('extranetUserAclEndUser.extranetUser = :current_extranet_user')
            ->andWhere(\sprintf('endUser = %s', $endUserAlias))
            ->getDQL();

        $queryBuilder
            ->leftJoin(\sprintf('%s.buyer', $rootAlias), $buyerAlias)
            ->leftJoin(\sprintf('%s.maintainer', $rootAlias), $maintainerAlias)
            ->leftJoin(\sprintf('%s.endUser', $rootAlias), $endUserAlias)

            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->exists($subQueryBuyer),
                $queryBuilder->expr()->exists($subQueryMaintainer),
                $queryBuilder->expr()->exists($subQueryEndUser),
            ))

            ->setParameter('current_extranet_user', $extranetUser);
    }

    /**
     * Restrict the collection to equipment records that are visible to extranet users, i.e. those
     * that have at least one of a ship date, a commissioning date or an actual delivery date set,
     * or that are IN PRODUCTION with a first green tag date already recorded.
     */
    private function addVisibleToExtranetFilter(QueryBuilder $queryBuilder): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->isNotNull(\sprintf('%s.dateShipped', $rootAlias)),
                $queryBuilder->expr()->isNotNull(\sprintf('%s.dateCommissioned', $rootAlias)),
                $queryBuilder->expr()->isNotNull(\sprintf('%s.actualDeliveryDate', $rootAlias)),
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(\sprintf('%s.status', $rootAlias), ':inProductionStatus'),
                    $queryBuilder->expr()->isNotNull(\sprintf('%s.firstGreenTagDate', $rootAlias)),
                ),
            ))
            ->setParameter('inProductionStatus', EquipmentRecordStatus::IN_PRODUCTION->value)
        ;
    }
}
