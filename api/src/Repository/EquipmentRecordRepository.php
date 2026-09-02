<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Sales\Product;
use App\Entity\Sales\ProductFamily;
use App\Entity\Support\Component;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class EquipmentRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipmentRecord::class);
    }

    public function getSerialNumbersForCustomer(Customer $customer): array
    {
        $qb = $this->createQueryBuilder('er');

        $qb
            ->select('er.serialNumber AS id')
            ->orWhere('er.buyer = :customer')
            ->orWhere('er.endUser = :customer')
            ->orWhere('er.maintainer = :customer')
            ->setParameter('customer', $customer)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForProduct(Product $product): array
    {
        $qb = $this->createQueryBuilder('er');

        $qb
            ->select('er.serialNumber AS id')
            ->where('er.product = :product')
            ->setParameter('product', $product);

        return $qb->getQuery()->getScalarResult();
    }

    public function getIdentifiersForProductFamily(ProductFamily $productFamily): array
    {
        $qb = $this->createQueryBuilder('er');

        $qb
            ->select('er.serialNumber AS id')
            ->join('er.product', 'p')
            ->Where('p.family = :productFamily')
            ->setParameter('productFamily', $productFamily);

        return $qb->getQuery()->getScalarResult();
    }

    public function getEquipmentByExtranetUserCrtCustomer(Customer $customer, string $project): ?EquipmentRecord
    {
        // A project can have several ERs attached (one per delivered unit); they share the same CBOM,
        // so any of them (the last one, deterministically) can be used.
        $qb = $this->createQueryBuilder('er');

        $qb
            ->Where('er.endUser = :customer')
            ->orWhere('er.buyer = :customer')
            ->orWhere('er.maintainer = :customer')
            ->andWhere('er.projectNumber = :project')
            ->setParameter('customer', $customer->getId())
            ->setParameter('project', $project)
            ->orderBy('er.id', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getEquipmentRecordLegacyIdsByExtranetUser(ExtranetUser $extranetUser): array
    {
        $qb = $this->getEquipmentRecordByExtranetUserQueryBuilder($extranetUser)
            ->select('er.legacyId');

        return array_column($qb->getQuery()->getScalarResult(), 'legacyId');
    }

    public function getEquipmentRecordSerialNumbersByExtranetUser(ExtranetUser $extranetUser): array
    {
        $qb = $this->getEquipmentRecordByExtranetUserQueryBuilder($extranetUser)
            ->select('er.serialNumber');

        return array_column($qb->getQuery()->getScalarResult(), 'serialNumber');
    }

    public function getEquipmentRecordsEstimatedGreenTagByMonth(\DateTime $month): array
    {
        $startOfMonth = (clone $month)->modify('first day of this month')->setTime(0, 0, 0);
        $endOfMonth = (clone $month)->modify('last day of this month')->setTime(23, 59, 59);

        $queryBuilder = $this->createQueryBuilder('er');

        $queryBuilder
            ->join('er.manufacturerLocation', 'loc')
            ->where('er.estimatedGreenTagDate >= :start')
            ->andWhere('er.estimatedGreenTagDate < :end')
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findEquipmentToSynchronizeWithLink(): array
    {
        $qb = $this->createQueryBuilder('er');
        $qb
            ->join('er.serials', 'serials')
            ->join('serials.component', 'component')
            ->where($qb->expr()->eq('component.name', ':link_component'))
            ->setParameter('link_component', Component::OBU_LINK)
        ;

        return $qb->getQuery()->getResult();
    }

    private function getEquipmentRecordByExtranetUserQueryBuilder(ExtranetUser $extranetUser): QueryBuilder
    {
        $mainQb = $this->createQueryBuilder('er');

        $subBuyer = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'acl_buyer')
            ->join('acl_buyer.crt', 'crt_buyer')
            ->where('acl_buyer.extranetUser = :user')
            ->andWhere('crt_buyer.customer = er.buyer')
            ->getDQL();

        $subEndUser = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'acl_endUser')
            ->join('acl_endUser.crt', 'crt_endUser')
            ->where('acl_endUser.extranetUser = :user')
            ->andWhere('crt_endUser.customer = er.endUser')
            ->getDQL();

        $subMaintainer = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from(ExtranetUserAcl::class, 'acl_maintainer')
            ->join('acl_maintainer.crt', 'crt_maintainer')
            ->where('acl_maintainer.extranetUser = :user')
            ->andWhere('crt_maintainer.customer = er.maintainer')
            ->getDQL();

        $mainQb
            ->andWhere(
                $mainQb->expr()->orX(
                    $mainQb->expr()->exists($subBuyer),
                    $mainQb->expr()->exists($subEndUser),
                    $mainQb->expr()->exists($subMaintainer)
                )
            )
            ->setParameter('user', $extranetUser->getId());

        return $mainQb;
    }
}
