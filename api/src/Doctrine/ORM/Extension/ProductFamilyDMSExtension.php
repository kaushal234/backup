<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ProductFamilyDMS;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class ProductFamilyDMSExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if (ProductFamilyDMS::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($user instanceof ExtranetUser) {
            $dmsAlias = $queryNameGenerator->generateJoinAlias('dms');
            $queryBuilder
                ->leftJoin(\sprintf('%s.dms', $rootAlias), $dmsAlias)
                ->andWhere($queryBuilder->expr()->in(\sprintf('%s.dmsType', $rootAlias), ':types'))
                ->andWhere($queryBuilder->expr()->eq(\sprintf('%s.portal', $dmsAlias), ':extranet'))
                ->andWhere($queryBuilder->expr()->eq(\sprintf('%s.confidential', $dmsAlias), ':confidential'))
                ->setParameter('types', [ProductFamilyDMS::DATASHEET, ProductFamilyDMS::FACT_SHEET])
                ->setParameter('extranet', 'EXTRANET')
                ->setParameter('confidential', false)
            ;
        }
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
