<?php

declare(strict_types=1);

namespace App\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\Directory\People;
use App\Entity\Report\ReportSnapshot;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ReportSnapshotExtension implements QueryCollectionExtensionInterface, ServiceSubscriberInterface
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
        if (ReportSnapshot::class !== $resourceClass || !$operation instanceof GetCollection) {
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

        if ('/mis/trouble_tickets' === ($context['filters']['resource'] ?? null)
            && 'status' === ($context['filters']['x'] ?? null)
            && 'module.application.name' === ($context['filters']['y'] ?? null)
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];

        $orStatements = $queryBuilder->expr()->orX();
        $orStatements->add($queryBuilder->expr()->andX(
            $queryBuilder->expr()->eq(\sprintf('%s.resource', $alias), ':dms'),
            $queryBuilder->expr()->eq(\sprintf('%s.x', $alias), ':owner'),
            $queryBuilder->expr()->eq(\sprintf('%s.y', $alias), ':status'),
        ));
        $queryBuilder->setParameters(new ArrayCollection([
            new Parameter('dms', '/dms'),
            new Parameter('owner', 'owner.businessUnit.name'),
            new Parameter('status', 'status'),
        ]));

        $queryBuilder->andWhere($orStatements);
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
        ];
    }
}
