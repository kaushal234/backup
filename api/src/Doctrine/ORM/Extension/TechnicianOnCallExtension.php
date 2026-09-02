<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class TechnicianOnCallExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface, ServiceSubscriberInterface
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
        if (TechnicianOnCall::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        if (!$user instanceof ExtranetUser) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $equipmentRecordAlias = $queryNameGenerator->generateJoinAlias('equipmentRecord');
        $queryBuilder->innerJoin(\sprintf('%s.equipmentRecord', $rootAlias), $equipmentRecordAlias);
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
            ->leftJoin(\sprintf('%s.buyer', $equipmentRecordAlias), $buyerAlias)
            ->leftJoin(\sprintf('%s.maintainer', $equipmentRecordAlias), $maintainerAlias)
            ->leftJoin(\sprintf('%s.endUser', $equipmentRecordAlias), $endUserAlias)
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->exists($subQueryBuyer),
                $queryBuilder->expr()->exists($subQueryMaintainer),
                $queryBuilder->expr()->exists($subQueryEndUser),
            ))
            ->setParameter('current_extranet_user', $user);

        $this->excludeCommissioning($queryBuilder, $queryNameGenerator);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (TechnicianOnCall::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        if (!$user instanceof ExtranetUser) {
            return;
        }

        $this->excludeCommissioning($queryBuilder, $queryNameGenerator);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }

    /**
     * Hide TechnicianOnCall with the "Commissioning" service activity from extranet users.
     */
    private function excludeCommissioning(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $serviceActivityAlias = $queryNameGenerator->generateJoinAlias('serviceActivity');
        $queryBuilder
            ->innerJoin(\sprintf('%s.serviceActivity', $rootAlias), $serviceActivityAlias)
            ->andWhere(\sprintf('%s.name != :excludedServiceActivity', $serviceActivityAlias))
            ->setParameter('excludedServiceActivity', ServiceActivity::COMMISSIONING);
    }
}
