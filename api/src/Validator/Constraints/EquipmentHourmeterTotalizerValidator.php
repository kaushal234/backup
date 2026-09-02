<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Support\EquipmentFollowUpReport;
use App\Repository\Support\EquipmentFollowUpReportRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EquipmentHourmeterTotalizerValidator extends ConstraintValidator
{
    private readonly EquipmentFollowUpReportRepository $repository;

    public function __construct(EquipmentFollowUpReportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validate($object, Constraint $constraint): void
    {
        if (!$constraint instanceof EquipmentHourmeterTotalizer || !$object instanceof EquipmentFollowUpReport) {
            return;
        }

        $equipment = $object->getEquipmentRecord();
        $hourmeterDate = $object->getHourmeterDate();

        if (null === $previous = $this->repository->getPreviousFollowUpReport($equipment, $hourmeterDate)) {
            return;
        }
        $totalizer = $object->getHourmeterTotalizer();

        if ($previous->getHourmeterTotalizer() > $totalizer) {
            $this->context->buildViolation($constraint->minMessage)
                ->setParameter('{{ value }}', (string) $totalizer)
                ->setParameter('{{ limit }}', (string) $previous->getHourmeterTotalizer())
                ->atPath($constraint->errorPath)
                ->addViolation();
        }

        if (null === $next = $this->repository->getNextFollowUpReport($equipment, $hourmeterDate)) {
            return;
        }

        if ($next->getHourmeterTotalizer() < $totalizer) {
            $this->context->buildViolation($constraint->maxMessage)
                ->setParameter('{{ value }}', (string) $totalizer)
                ->setParameter('{{ limit }}', (string) $next->getHourmeterTotalizer())
                ->atPath($constraint->errorPath)
                ->addViolation();
        }
    }
}
