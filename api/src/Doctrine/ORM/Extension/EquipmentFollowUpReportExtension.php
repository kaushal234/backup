<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Support\EquipmentFollowUpReport;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class EquipmentFollowUpReportExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
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
        if (EquipmentFollowUpReport::class !== $resourceClass) {
            return;
        }

        $user = $this->container->get(Security::class)->getUser();
        if ($user instanceof ExtranetUser) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $erAlias = $queryNameGenerator->generateJoinAlias('equipmentRecord');
            $contractAlias = $queryNameGenerator->generateJoinAlias('contracts');
            $endUserAlias = $queryNameGenerator->generateJoinAlias('endUserRepresentatives');
            $buyerAlias = $queryNameGenerator->generateJoinAlias('buyerRepresentatives');
            $queryBuilder
                ->innerJoin(\sprintf('%s.equipmentRecord', $rootAlias), $erAlias)
                ->leftJoin(\sprintf('%s.contracts', $erAlias), $contractAlias)
                ->leftJoin(\sprintf('%s.endUserRepresentatives', $contractAlias), $endUserAlias)
                ->leftJoin(\sprintf('%s.buyerRepresentatives', $contractAlias), $buyerAlias)
                ->andWhere(\sprintf('((%1$s.createdBy = :current_extranet_user AND (%2$s.id IS NULL OR :today < %2$s.startDate OR  %2$s.expirationDate < :today)) OR ((%3$s.id = :current_extranet_user OR %4$s.id = :current_extranet_user) AND %2$s.startDate <= :today AND :today <= %2$s.expirationDate))', $rootAlias, $contractAlias, $endUserAlias, $buyerAlias))
                ->setParameter('current_extranet_user', $user)
                ->setParameter('today', new \DateTime())
            ;
        }
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
