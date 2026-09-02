<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\SalesForecast;
use App\Repository\Directory\PeopleRepository;
use App\Repository\FeatureRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SalesForecastExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if (SalesForecast::class !== $resourceClass || !$operation instanceof GetCollection || 'my_area' === $operation->getName()) {
            return;
        }

        $security = $this->container->get(Security::class);

        if (null === $user = $security->getUser()) {
            return;
        }

        if (!$user instanceof People) {
            $queryBuilder->where('1=0');

            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $orStatements = $queryBuilder->expr()->orX();

        if ($access = $security->isGranted('FEATURE_SALES_FORECAST_VIEW_FULL')) {
            return;
        }

        if ($security->isGranted('MOO_SFR')) {
            return;
        }

        if ($security->isGranted('FEATURE_SALES_FORECAST_VIEW_ASM')) {
            $orStatements->add($queryBuilder->expr()->eq(\sprintf('%s.asm', $alias), $user->getId()));
            $asmAlias = $queryNameGenerator->generateJoinAlias('asm');
            $queryBuilder->leftJoin(\sprintf('%s.asm', $alias), $asmAlias);
            $orStatements->add($queryBuilder->expr()->eq(\sprintf('%s.supervisor', $asmAlias), $user->getId()));
            $asmSupervisorAlias = $queryNameGenerator->generateJoinAlias('supervisor');
            $queryBuilder->leftJoin(\sprintf('%s.supervisor', $asmAlias), $asmSupervisorAlias);
            $orStatements->add($queryBuilder->expr()->eq(\sprintf('%s.supervisor', $asmSupervisorAlias), $user->getId()));
            $access = true;
        }

        if ($security->isGranted('FEATURE_SALES_FORECAST_VIEW_CUSTOMER')) {
            $hierarchy = $this->container->get(PeopleRepository::class)->getSubordinates($user, 3);
            $hierarchy[] = $user;
            $sfrBuyerAlias = $queryNameGenerator->generateJoinAlias('buyer');
            $sfrEndUserAlias = $queryNameGenerator->generateJoinAlias('endUser');
            $queryBuilder->leftJoin(\sprintf('%s.buyer', $alias), $sfrBuyerAlias);
            $queryBuilder->leftJoin(\sprintf('%s.endUser', $alias), $sfrEndUserAlias);
            $sfrBuyerMainSalesRepresentativeAlias = $queryNameGenerator->generateJoinAlias('buyerMainSalesRepresentative');
            $sfrEndUserMainSalesRepresentativeAlias = $queryNameGenerator->generateJoinAlias('endUserMainSalesRepresentative');
            $sfrBuyerSecondarySalesRepresentativesAlias = $queryNameGenerator->generateJoinAlias('buyerSecondarySalesRepresentatives');
            $sfrEndUserSecondarySalesRepresentativesAlias = $queryNameGenerator->generateJoinAlias('endUserSecondarySalesRepresentatives');
            $queryBuilder->leftJoin(\sprintf('%s.mainSalesRepresentative', $sfrBuyerAlias), $sfrBuyerMainSalesRepresentativeAlias);
            $queryBuilder->leftJoin(\sprintf('%s.mainSalesRepresentative', $sfrEndUserAlias), $sfrEndUserMainSalesRepresentativeAlias);
            $queryBuilder->leftJoin(\sprintf('%s.secondarySalesRepresentatives', $sfrBuyerAlias), $sfrBuyerSecondarySalesRepresentativesAlias);
            $queryBuilder->leftJoin(\sprintf('%s.secondarySalesRepresentatives', $sfrEndUserAlias), $sfrEndUserSecondarySalesRepresentativesAlias);
            $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.asm', $sfrBuyerMainSalesRepresentativeAlias), ':hierarchy'));
            $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.asm', $sfrEndUserMainSalesRepresentativeAlias), ':hierarchy'));
            $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.asm', $sfrBuyerSecondarySalesRepresentativesAlias), ':hierarchy'));
            $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.asm', $sfrEndUserSecondarySalesRepresentativesAlias), ':hierarchy'));

            for ($i = 2; $i <= 3; ++$i) {
                $parentBuyerAlias = $sfrBuyerAlias;
                $parentEndUserAlias = $sfrEndUserAlias;
                $sfrBuyerAlias = $queryNameGenerator->generateJoinAlias('parentCustomer');
                $sfrEndUserAlias = $queryNameGenerator->generateJoinAlias('parentCustomer');
                $queryBuilder->leftJoin(\sprintf('%s.parentCustomer', $parentBuyerAlias), $sfrBuyerAlias);
                $queryBuilder->leftJoin(\sprintf('%s.parentCustomer', $parentEndUserAlias), $sfrEndUserAlias);
                $sfrBuyerMainSalesRepresentativeAlias = $queryNameGenerator->generateJoinAlias('buyerMainSalesRepresentative');
                $sfrEndUserMainSalesRepresentativeAlias = $queryNameGenerator->generateJoinAlias('endUserMainSalesRepresentative');
                $sfrBuyerSecondarySalesRepresentativesAlias = $queryNameGenerator->generateJoinAlias('buyerSecondarySalesRepresentatives');
                $sfrEndUserSecondarySalesRepresentativesAlias = $queryNameGenerator->generateJoinAlias('endUserSecondarySalesRepresentatives');
                $queryBuilder->leftJoin(\sprintf('%s.mainSalesRepresentative', $sfrBuyerAlias), $sfrBuyerMainSalesRepresentativeAlias);
                $queryBuilder->leftJoin(\sprintf('%s.mainSalesRepresentative', $sfrEndUserAlias), $sfrEndUserMainSalesRepresentativeAlias);
                $queryBuilder->leftJoin(\sprintf('%s.secondarySalesRepresentatives', $sfrBuyerAlias), $sfrBuyerSecondarySalesRepresentativesAlias);
                $queryBuilder->leftJoin(\sprintf('%s.secondarySalesRepresentatives', $sfrEndUserAlias), $sfrEndUserSecondarySalesRepresentativesAlias);
                $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.asm', $sfrBuyerMainSalesRepresentativeAlias), ':hierarchy'));
                $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.asm', $sfrEndUserMainSalesRepresentativeAlias), ':hierarchy'));
                $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.asm', $sfrBuyerSecondarySalesRepresentativesAlias), ':hierarchy'));
                $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.asm', $sfrEndUserSecondarySalesRepresentativesAlias), ':hierarchy'));
            }

            $queryBuilder->setParameter('hierarchy', $hierarchy);

            $access = true;
        }

        $grantedSso = $security->isGranted('FEATURE_SALES_FORECAST_VIEW_SSO');
        $grantedFactory = $security->isGranted('FEATURE_SALES_FORECAST_VIEW_FACTORY');
        $features = [];
        if ($grantedSso || $grantedFactory) {
            /** @var FeatureRepository $featureRepository */
            $featureRepository = $this->container->get(FeatureRepository::class);
            $features = $featureRepository->loadFeaturesByPeople($user);
            $access = true;
        }

        if ($grantedSso) {
            $ssos = array_column(array_filter($features, static function (array $feature) {
                return 'FEATURE_SALES_FORECAST_VIEW_SSO' === $feature['name'];
            }), 'location_id');

            $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.sso', $alias), ':sso_ids'));
            $queryBuilder->setParameter('sso_ids', $ssos);
        }

        if ($grantedFactory) {
            $factories = array_column(array_filter($features, static function (array $feature) {
                return 'FEATURE_SALES_FORECAST_VIEW_FACTORY' === $feature['name'];
            }), 'location_id');

            $orStatements->add($queryBuilder->expr()->in(\sprintf('%s.factory', $alias), ':factory_ids'));
            $queryBuilder->setParameter('factory_ids', $factories);
        }

        if ($security->isGranted('FEATURE_SALES_FORECAST_VIEW_MILITARY')) {
            $buyerAlias = $queryNameGenerator->generateJoinAlias('buyer');
            $endUserAlias = $queryNameGenerator->generateJoinAlias('endUser');
            $typeEndUserAlias = $queryNameGenerator->generateJoinAlias('type');
            $typeBuyerAlias = $queryNameGenerator->generateJoinAlias('type');
            $queryBuilder
                ->leftJoin(\sprintf('%s.endUser', $alias), $endUserAlias)
                ->leftJoin(\sprintf('%s.buyer', $alias), $buyerAlias)
                ->leftJoin(\sprintf('%s.customerTypes', $endUserAlias), $typeEndUserAlias)
                ->leftJoin(\sprintf('%s.customerTypes', $buyerAlias), $typeBuyerAlias)
            ;

            $orStatements->add($queryBuilder->expr()->eq(\sprintf('%s.name', $typeEndUserAlias), ':military_type'));
            $orStatements->add($queryBuilder->expr()->eq(\sprintf('%s.name', $typeBuyerAlias), ':military_type'));

            $queryBuilder->setParameter('military_type', CustomerType::MILITARY_TYPE_NAME);

            $access = true;
        }

        if ($access) {
            $userRegion = $user->getBusinessUnit()?->getRegion();
            if (null !== $userRegion) {
                $ssoRegionAlias = $queryNameGenerator->generateJoinAlias('sso');
                $ssoBuAlias = $queryNameGenerator->generateJoinAlias('businessUnit');
                $regionAlias = $queryNameGenerator->generateJoinAlias('region');
                $queryBuilder
                    ->leftJoin(\sprintf('%s.sso', $alias), $ssoRegionAlias)
                    ->leftJoin(\sprintf('%s.businessUnit', $ssoRegionAlias), $ssoBuAlias)
                    ->leftJoin(\sprintf('%s.region', $ssoBuAlias), $regionAlias);
                $orStatements->add($queryBuilder->expr()->eq(\sprintf('%s.id', $regionAlias), ':user_region_id'));
                $queryBuilder->setParameter('user_region_id', $userRegion->getId());
            }
        }

        if (!$access) {
            $subscriptionAlias = $queryNameGenerator->generateJoinAlias('subscription');
            $queryBuilder->leftJoin(Subscription::class, $subscriptionAlias, Join::WITH, \sprintf("CONCAT('%s', %s.id) = %s.resource", '/sales/sales_forecasts/', $alias, $subscriptionAlias));
            $orStatements->add(\sprintf('%s.user = :user', $subscriptionAlias));

            $queryBuilder->andWhere($orStatements)->setParameter('user', $user);

            return;
        }

        $queryBuilder->andWhere($orStatements);
    }

    public static function getSubscribedServices(): array
    {
        return [
            PeopleRepository::class,
            Security::class,
            FeatureRepository::class,
        ];
    }
}
