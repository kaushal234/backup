<?php

declare(strict_types=1);

namespace LegacyBundle\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\ServiceActivity;
use App\Repository\EquipmentRecordRepository;
use Doctrine\ORM\QueryBuilder;
use LegacyBundle\Entity\Quality\WarrantyClaim;
use LegacyBundle\Entity\TOC;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

final readonly class WarrantyClaimExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface, ServiceSubscriberInterface
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->applyWarrantyClaimSecurity($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->applyWarrantyClaimSecurity($queryBuilder, $resourceClass);
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
            EquipmentRecordRepository::class,
        ];
    }

    private function applyWarrantyClaimSecurity(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (WarrantyClaim::class !== $resourceClass) {
            return;
        }

        $security = $this->container->get(Security::class);
        $user = $security->getUser();

        if (!$user instanceof ExtranetUser) {
            return;
        }

        $equipmentRecordRepository = $this->container->get(EquipmentRecordRepository::class);
        $serialNumbersFromEquipmentRecords = $equipmentRecordRepository->getEquipmentRecordSerialNumbersByExtranetUser($user);

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(\sprintf('%s.serialNumber IN (:serialNumbersFromEquipmentRecords)', $rootAlias))
            ->setParameter('serialNumbersFromEquipmentRecords', $serialNumbersFromEquipmentRecords);

        $this->excludeWarrantiesFromCommissioning($queryBuilder, $rootAlias);
        $this->excludeWarrantiesFromConfidentialToc($queryBuilder, $rootAlias);
    }

    /**
     * Hide warranties linked to a confidential TechnicianOnCall (notification != 'Y') from extranet users.
     */
    private function excludeWarrantiesFromConfidentialToc(QueryBuilder $queryBuilder, string $rootAlias): void
    {
        $confidentialTocSubQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(TOC::class, 'confidential_toc')
            ->where(\sprintf('confidential_toc.warrantyId = %s.id', $rootAlias))
            ->andWhere('confidential_toc.notificationFlag != :confidentialNotification');

        $queryBuilder
            ->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($confidentialTocSubQuery->getDQL())))
            ->setParameter('confidentialNotification', 'Y');
    }

    /**
     * Hide warranties created from a "Commissioning" TechnicianOnCall from extranet users.
     */
    private function excludeWarrantiesFromCommissioning(QueryBuilder $queryBuilder, string $rootAlias): void
    {
        $commissioningTocSubQuery = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(TOC::class, 'commissioning_toc')
            ->where(\sprintf('commissioning_toc.warrantyId = %s.id', $rootAlias))
            ->andWhere('commissioning_toc.activityType = :commissioningActivity');

        $queryBuilder
            ->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($commissioningTocSubQuery->getDQL())))
            ->setParameter('commissioningActivity', ServiceActivity::COMMISSIONING);
    }
}
