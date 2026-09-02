<?php

declare(strict_types=1);

namespace App\Manager\Service;

use App\Entity\EquipmentRecord;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;

readonly class BlackCat
{
    private const NUMBER_AUTHORIZED_BY_YEAR = 10;
    private const MIN_TOC_COUNT_UNDER_ONE_YEAR = 6;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function isBlackCat(EquipmentRecord $equipmentRecord): bool
    {
        $referenceDate = $equipmentRecord->getDateCommissioned();

        if (null === $referenceDate) {
            $shippedDate = $equipmentRecord->getDateShipped();
            if (null === $shippedDate) {
                return false;
            }
            // No commissioning date: use shipped date + 90 days as proxy (standard commissioning lead time)
            $referenceDate = \DateTime::createFromInterface($shippedDate)->modify('+90 days');
        }

        $now = new \DateTime();
        $dateDiff = $referenceDate->diff($now);

        $repository = $this->entityManager->getRepository(TechnicianOnCall::class);

        if (0 === $dateDiff->y) {
            // < 1 year: full-lifetime window — no 12-month history yet, so we use all available data
            $technicianOnCalls = $repository->countByEquipmentRecordAndCreatedDate($equipmentRecord, $referenceDate, [ServiceActivity::TROUBLESHOOTING]);

            if ($technicianOnCalls < self::MIN_TOC_COUNT_UNDER_ONE_YEAR) {
                return false;
            }

            $totalMonths = $dateDiff->m > 0 ? $dateDiff->m : 1;

            return ($technicianOnCalls / $totalMonths) > (self::NUMBER_AUTHORIZED_BY_YEAR / 12);
        }

        // >= 1 year: rolling 12-month window only — past history beyond one year is intentionally ignored
        $referenceDate = (new \DateTime('-1 year'));
        $technicianOnCalls = $repository->countByEquipmentRecordAndCreatedDate($equipmentRecord, $referenceDate, [ServiceActivity::TROUBLESHOOTING]);

        return ($technicianOnCalls / 12) >= (self::NUMBER_AUTHORIZED_BY_YEAR / 12);
    }
}
