<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\EquipmentRecord;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Entity\EquipmentRecord as LegacyEquipmentRecord;
use LegacyBundle\Entity\WarrantyClaim;
use LegacyBundle\Factory\WarrantyFactory;

class WarrantyClaimManager
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityManagerInterface $legacyEntityManager,
        private readonly WarrantyFactory $warrantyFactory,
        private readonly ModLinkManager $modLinkManager,
    ) {
    }

    public function findOneById(int $id)
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->select('id')
            ->addSelect('warranty_status as status')
            ->addSelect('entered_by as enteredBy')
            ->addSelect('claimant_details as claimantDetails')
            ->addSelect('warranty_details as warrantyDetails')
            ->addSelect('customer_name as customer')
            ->addSelect('problem_desc as problemDescription')
            ->addSelect('claim_date as claimDate')
            ->addSelect('type')
            ->addSelect('model')
            ->addSelect('man_location as manufacturerLocation')
            ->addSelect('sales_org as salesOrganization')
            ->addSelect('serial_number as serialNumber')
            ->addSelect('equipment_location as equipmentLocation')
            ->addSelect('hours')
            ->from('warranty')
            ->where('id = :id')
            ->setParameter('id', $id)
        ;

        $result = $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());

        return $result->fetchAssociative();
    }

    public function findFilesById(int $id, bool $onlyPublic = false)
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->select('id')
            ->addSelect('date')
            ->addSelect('description')
            ->addSelect('filename')
            ->from('warranty_files')
            ->where('parent_id = :id')
            ->setParameter('id', $id)
        ;

        if ($onlyPublic) {
            $queryBuilder->andWhere('public = 1');
        }

        $result = $this->legacyConnection->executeQuery($queryBuilder->getSQL(), $queryBuilder->getParameters());

        return $result->fetchAllAssociative();
    }

    public function getWarrantyStatus(EquipmentRecord $equipmentRecord)
    {
        $repository = $this->legacyEntityManager->getRepository(LegacyEquipmentRecord::class);

        return $repository->findWarranty($equipmentRecord->getLegacyId());
    }

    public function findById(?int $warrantyLegacyId): ?WarrantyClaim
    {
        if (!$warrantyLegacyId) {
            return null;
        }

        return $this->legacyEntityManager->getRepository(WarrantyClaim::class)->find($warrantyLegacyId);
    }

    public function createFromTechnicianOnCall(TechnicianOnCall $technicianOnCall)
    {
        $warranty = $this->warrantyFactory->createFromTechnicianOnCall($technicianOnCall);

        $this->legacyEntityManager->persist($warranty);
        $this->legacyEntityManager->flush();

        $this->modLinkManager->createLink($technicianOnCall->getLegacyId(), TechnicianOnCall::MODULE_NAME, $warranty->getId(), 'WC');

        return $warranty;
    }

    public function updateStatus(WarrantyClaim $warrantyClaim): void
    {
        $tableName = $this->legacyEntityManager->getClassMetadata(WarrantyClaim::class)->getTableName();

        $this->legacyEntityManager->getConnection()->createQueryBuilder()
            ->update($tableName)
            ->set('warranty_status', ':status')
            ->where('id = :id')
            ->setParameter('status', $warrantyClaim->status)
            ->setParameter('id', $warrantyClaim->getId())
            ->executeStatement()
        ;
    }
}
