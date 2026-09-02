<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Support\EquipmentSerial;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class EquipmentSerialExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
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
        if (EquipmentSerial::class !== $resourceClass) {
            return;
        }

        /** @var Security $security */
        $security = $this->container->get(Security::class);
        $user = $security->getUser();

        if (!$user instanceof ExtranetUser) {
            return;
        }

        $this->addExtranetUserAccessRestriction($queryBuilder, $queryNameGenerator, $user);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    /**
     * Restrict EquipmentSerial collection to those whose parent EquipmentRecord
     * is accessible by the current ExtranetUser (buyer / maintainer / endUser).
     */
    private function addExtranetUserAccessRestriction(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        ExtranetUser $extranetUser,
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $equipmentRecordAlias = $queryNameGenerator->generateJoinAlias('equipmentRecord');

        $buyerAlias = $queryNameGenerator->generateJoinAlias('buyer');
        $maintainerAlias = $queryNameGenerator->generateJoinAlias('maintainer');
        $endUserAlias = $queryNameGenerator->generateJoinAlias('endUser');

        $queryBuilder
            ->leftJoin(\sprintf('%s.equipmentRecord', $rootAlias), $equipmentRecordAlias)
            ->leftJoin(\sprintf('%s.buyer', $equipmentRecordAlias), $buyerAlias)
            ->leftJoin(\sprintf('%s.maintainer', $equipmentRecordAlias), $maintainerAlias)
            ->leftJoin(\sprintf('%s.endUser', $equipmentRecordAlias), $endUserAlias);

        $subQueryBuyer = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'aclBuyer')
            ->innerJoin('aclBuyer.crt', 'crtBuyer')
            ->innerJoin('crtBuyer.customer', 'buyer')
            ->where('aclBuyer.extranetUser = :current_extranet_user')
            ->andWhere(\sprintf('buyer = %s', $buyerAlias))
            ->getDQL();

        $subQueryMaintainer = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'aclMaintainer')
            ->innerJoin('aclMaintainer.crt', 'crtMaintainer')
            ->innerJoin('crtMaintainer.customer', 'maintainer')
            ->where('aclMaintainer.extranetUser = :current_extranet_user')
            ->andWhere(\sprintf('maintainer = %s', $maintainerAlias))
            ->getDQL();

        $subQueryEndUser = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'aclEndUser')
            ->innerJoin('aclEndUser.crt', 'crtEndUser')
            ->innerJoin('crtEndUser.customer', 'endUser')
            ->where('aclEndUser.extranetUser = :current_extranet_user')
            ->andWhere(\sprintf('endUser = %s', $endUserAlias))
            ->getDQL();

        $expr = $queryBuilder->expr();

        $queryBuilder
            ->andWhere(
                $expr->orX(
                    $expr->exists($subQueryBuyer),
                    $expr->exists($subQueryMaintainer),
                    $expr->exists($subQueryEndUser),
                )
            )
            ->setParameter('current_extranet_user', $extranetUser);
    }
}
