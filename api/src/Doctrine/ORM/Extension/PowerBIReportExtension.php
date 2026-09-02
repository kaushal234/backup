<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Acl;
use App\Entity\PowerBI\Report;
use App\Entity\User;
use App\Manager\Directory\PeopleManager;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * This doctrine extension is use as a default filter for power BI reports.
 * We return all reports with no ACL groups attached and also only reports when acls groups match user permissions.
 */
class PowerBIReportExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (Report::class !== $resourceClass) {
            return;
        }

        if (null === $user = $this->container->get(Security::class)->getUser()) {
            return;
        }

        if (PeopleManager::hasGroup($user, 'SUPERUSER')) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->leftJoin($alias.'.groups', 'groups')
            ->andWhere($queryBuilder->expr()->in('groups', ':groups'))
            ->setParameter('groups', $user->getValidGroups())
            ->orWhere($queryBuilder->expr()->isNull('groups'))
        ;
    }

    public static function getSubscribedServices(): array
    {
        return [Security::class];
    }
}
