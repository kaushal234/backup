<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Acl;
use App\Entity\Directory\People;
use App\Entity\Finance\AccountReceivable;
use App\Entity\Finance\InvoiceRecord;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class AccountReceivableExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
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
        if (!\in_array($resourceClass, [AccountReceivable::class, InvoiceRecord::class], true) || !$operation instanceof GetCollection) {
            return;
        }

        $security = $this->container->get(Security::class);
        $user = $security->getUser();
        if (!$user instanceof People) {
            $queryBuilder->where('1=0');

            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $orStatements = $queryBuilder->expr()->orX();

        if ($security->isGranted('FEATURE_ACCOUNT_RECEIVABLES_VIEW_FULL') || $security->isGranted('MOO_AR')) {
            return;
        }

        if ($access = $security->isGranted('FEATURE_ACCOUNT_RECEIVABLES_VIEW_SSO')) {
            $customerErpReferenceAlias = $queryNameGenerator->generateJoinAlias('cus_erp');
            $ssoAlias = $queryNameGenerator->generateJoinAlias('sso');
            $aclsAlias = $queryNameGenerator->generateJoinAlias('acls');
            $groupsAlias = $queryNameGenerator->generateJoinAlias('group');
            $featuresAlias = $queryNameGenerator->generateJoinAlias('features');
            $queryBuilder
                ->leftJoin(\sprintf('%s.customerErpReference', $alias), $customerErpReferenceAlias)
                ->leftJoin(\sprintf('%s.sso', $customerErpReferenceAlias), $ssoAlias)
                ->leftJoin(Acl::class, $aclsAlias, Join::WITH, \sprintf('%1$s.id = %2$s.location AND %2$s.user = %3$s', $ssoAlias, $aclsAlias, $user->getId()))
                ->leftJoin(\sprintf('%s.group', $aclsAlias), $groupsAlias)
                ->leftJoin(\sprintf('%s.features', $groupsAlias), $featuresAlias)
            ;

            $orStatements->add($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq(\sprintf('%s.name', $featuresAlias), ':feature_sso'),
                $queryBuilder->expr()->eq(\sprintf('%s.location', $aclsAlias), \sprintf('%s.sso', $customerErpReferenceAlias))
            ));

            $queryBuilder->setParameter('feature_sso', 'FEATURE_ACCOUNT_RECEIVABLES_VIEW_SSO');
            $queryBuilder->groupBy(\sprintf('%s.id', $alias));
            $access = true;
        }

        if (!$access) {
            $queryBuilder->andWhere('1=0');

            return;
        }

        $queryBuilder->andWhere($orStatements);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
