<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SalesForecastMyAreaExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
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
        if (SalesForecast::class !== $resourceClass || 'my_area' !== $operation->getName()) {
            return;
        }

        if (null === $user = $this->container->get(Security::class)->getUser()) {
            return;
        }

        if (!$user instanceof People) {
            $queryBuilder->where('1=0');

            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $asmAlias = $queryNameGenerator->generateJoinAlias('asm');
        $countryAlias = $queryNameGenerator->generateJoinAlias('country');
        $salesAreasAlias = $queryNameGenerator->generateJoinAlias('salesAreas');
        $salesAreasSSOAlias = $queryNameGenerator->generateJoinAlias('salesAreasSSO');
        $asmBusinessUnitAlias = $queryNameGenerator->generateJoinAlias('asmBusinessUnit');
        $asmLocationAlias = $queryNameGenerator->generateJoinAlias('asmLocation');

        $queryBuilder
            ->leftJoin(\sprintf('%s.asm', $alias), $asmAlias)
            ->leftJoin(\sprintf('%s.businessUnit', $asmAlias), $asmBusinessUnitAlias)
            ->leftJoin(\sprintf('%s.location', $asmBusinessUnitAlias), $asmLocationAlias)
            ->leftJoin(\sprintf('%s.country', $alias), $countryAlias)
            ->leftJoin(\sprintf('%s.salesAreas', $countryAlias), $salesAreasAlias)
            ->leftJoin(\sprintf('%s.sso', $salesAreasAlias), $salesAreasSSOAlias)
        ;

        $queryBuilder->andWhere($queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq(\sprintf('%s.asm', $salesAreasAlias), $user->getId()),
            \sprintf('%s.asm != :current_user', $alias)
        ));

        $queryBuilder->setParameter('current_user', $user);
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
