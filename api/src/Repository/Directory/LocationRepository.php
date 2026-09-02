<?php

declare(strict_types=1);

namespace App\Repository\Directory;

use App\Entity\Acl;
use App\Entity\Directory\JuridicalLocation;
use App\Entity\Directory\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function getErpCodes(): array
    {
        return array_map('current', $this->getBaseQueryBuilder()->getQuery()->getResult());
    }

    public function getTimezoneByErpCodes(): array
    {
        $qb = $this->getBaseQueryBuilder()
            ->addSelect('l.timeZone')
        ;

        return array_column($qb->getQuery()->getResult(), 'timeZone', 'erp');
    }

    public function getFactoryWarehouseErpCodes(): array
    {
        $qb = $this->getBaseQueryBuilder()
            ->andWhere('l.capability.factory = :isFactory')
            ->andWhere('l.capability.warehouse = :isWarehouse')
            ->setParameter('isFactory', true)
            ->setParameter('isWarehouse', true)
        ;

        return array_map('current', $qb->getQuery()->getResult());
    }

    public function getFactoryOrWarehouseErpCodes(): array
    {
        $qb = $this->getBaseQueryBuilder()
            ->andWhere('l.capability.factory = :isFactory OR l.capability.warehouse = :isWarehouse')
            ->setParameter('isFactory', true)
            ->setParameter('isWarehouse', true)
        ;

        return array_map('current', $qb->getQuery()->getResult());
    }

    /**
     * @return Location[]
     */
    public function findPublic()
    {
        $qb = $this->createQueryBuilder('l');

        $qb
            ->select('l')
            ->andWhere('l.state.public = :public')
            ->andWhere('l.state.disabled = :disabled')
        ;

        $qb->setParameters(new ArrayCollection([
            new Parameter('public', true),
            new Parameter('disabled', false),
        ]));

        return $qb->getQuery()->getResult();
    }

    public function getIdentifiersForJuridicalLocation(JuridicalLocation $juridicalLocation): array
    {
        $qb = $this->createQueryBuilder('l');

        $qb
            ->select('l.id')
            ->where('l.juridicalLocation = :juridicalLocation')
            ->setParameter('juridicalLocation', $juridicalLocation)
        ;

        return $qb->getQuery()->getScalarResult();
    }

    /**
     * Get list of location from user depending on his associated features.
     */
    public function findByUserAndFeatures(UserInterface $user, array $features): array
    {
        $qb = $this->createQueryBuilder('location');

        $qb->innerJoin(Acl::class, 'acl', Join::WITH, 'acl.location = location')
            ->innerJoin('acl.group', 'groups')
            ->innerJoin('groups.features', 'features')
            ->where('acl.user = :user')
            ->andWhere('features.name IN (:features)')
            ->setParameter('user', $user)
            ->setParameter('features', $features);

        return $qb->getQuery()->getResult();
    }

    private function getBaseQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('l')
            ->select('l.erp')
            ->where('l.erp IS NOT NULL')
            ->andWhere('l.erp != 0')
            ->andWhere('l.erpInLN = :inLN')
            ->setParameter('inLN', true)
            ->distinct()
        ;
    }
}
