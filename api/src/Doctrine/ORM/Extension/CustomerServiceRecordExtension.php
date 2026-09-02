<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class CustomerServiceRecordExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
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
        if (AbstractCustomerServiceRecord::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        $rootAlias = $queryBuilder->getRootAliases()[0];
        if ($user instanceof ExtranetUser) {
            $equipmentRecordAlias = $queryNameGenerator->generateJoinAlias('equipmentRecord');
            $endUserAlias = $queryNameGenerator->generateJoinAlias('endUser');
            $crtAlias = $queryNameGenerator->generateJoinAlias('crt');
            $aclsAlias = $queryNameGenerator->generateJoinAlias('acls');
            $queryBuilder
                ->innerJoin(\sprintf('%s.equipmentRecord', $rootAlias), $equipmentRecordAlias)
                ->innerJoin(\sprintf('%s.endUser', $equipmentRecordAlias), $endUserAlias)
                ->innerJoin(\sprintf('%s.crt', $endUserAlias), $crtAlias)
                ->innerJoin(\sprintf('%s.acls', $crtAlias), $aclsAlias)
                ->andWhere(\sprintf('%s.extranetUser = :current_extranet_user', $aclsAlias))
                ->setParameter('current_extranet_user', $user)
            ;
        }
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
