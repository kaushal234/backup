<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecord;
use App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine;
use App\Repository\Sales\EquipmentShippingRecordLineRepository;
use App\Repository\Sales\PlanningDailyLimitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EquipmentShippingRecordLineLoadValidator extends ConstraintValidator
{
    public function __construct(
        protected RequestStack $requestStack,
        protected EquipmentShippingRecordLineRepository $equipmentShippingRecordLineRepository,
        protected EntityManagerInterface $entityManager,
        protected PlanningDailyLimitRepository $planningDailyLimitRepository,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof EquipmentShippingRecordLineLoad || !$value instanceof EquipmentShippingRecord) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return;
        }

        $factoryLoad = [];

        $newCollection = $value->getEquipmentShippingRecordLines();
        if ($request->isMethod(Request::METHOD_PUT)) {
            $uow = $this->entityManager->getUnitOfWork();
            $uow->computeChangeSets();
            if (\array_key_exists(EquipmentShippingRecordLine::class, $uow->getIdentityMap())) {
                $oldCollection = $uow->getIdentityMap()[EquipmentShippingRecordLine::class];
                foreach ($oldCollection as $oldItem) {
                    if (!$newCollection->contains($oldItem) && null !== $oldItem->estimatedPickUpDate) {
                        $factory = $oldItem->equipmentRecord->getManufacturerLocation();
                        $oldEstimatedPickUpDate = $oldItem->estimatedPickUpDate->format('Y-m-d');
                        $factoryLoad[$factory->getId()][$oldEstimatedPickUpDate] = (null === ($factoryLoad[$factory->getId()][$oldEstimatedPickUpDate] ?? null) ? -1 : $factoryLoad[$factory->getId()][$oldEstimatedPickUpDate] - 1);
                    }
                }
            }
            foreach ($newCollection as $line) {
                $changeSet = $uow->getEntityChangeSet($line);
                if (($factory = $line->equipmentRecord->getManufacturerLocation()) === null) {
                    continue;
                }
                if (null === ($changeSet['estimatedPickUpDate'] ?? null)) {
                    continue;
                }
                if ($changeSet['estimatedPickUpDate'][0]?->format('Y-m-d') === ($newEstimatedPickUpDate = $line->estimatedPickUpDate?->format('Y-m-d'))) {
                    continue;
                }
                if (null === $newEstimatedPickUpDate) {
                    $factoryLoad[$factory->getId()][$newEstimatedPickUpDate] = (null === ($factoryLoad[$factory->getId()][$newEstimatedPickUpDate] ?? null) ? -1 : $factoryLoad[$factory->getId()][$newEstimatedPickUpDate] - 1);
                    continue;
                }
                if (($oldEstimatedPickUpDate = $changeSet['estimatedPickUpDate'][0]?->format('Y-m-d')) === null) {
                    $factoryLoad[$factory->getId()][$newEstimatedPickUpDate] = (null === ($factoryLoad[$factory->getId()][$newEstimatedPickUpDate] ?? null) ? 1 : $factoryLoad[$factory->getId()][$newEstimatedPickUpDate] + 1);
                    continue;
                }
                $factoryLoad[$factory->getId()][$oldEstimatedPickUpDate] = (null === ($factoryLoad[$factory->getId()][$oldEstimatedPickUpDate] ?? null) ? -1 : $factoryLoad[$factory->getId()][$oldEstimatedPickUpDate] - 1);
                $factoryLoad[$factory->getId()][$newEstimatedPickUpDate] = (null === ($factoryLoad[$factory->getId()][$newEstimatedPickUpDate] ?? null) ? 1 : $factoryLoad[$factory->getId()][$newEstimatedPickUpDate] + 1);
            }
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            foreach ($newCollection as $line) {
                if (($factory = $line->equipmentRecord->getManufacturerLocation()) === null) {
                    continue;
                }
                if (null === ($newEstimatedPickUpDate = $line->estimatedPickUpDate?->format('Y-m-d'))) {
                    continue;
                }
                $factoryLoad[$factory->getId()][$newEstimatedPickUpDate] = (null === ($factoryLoad[$factory->getId()][$newEstimatedPickUpDate] ?? null) ? 1 : $factoryLoad[$factory->getId()][$newEstimatedPickUpDate] + 1);
            }
        }

        foreach ($factoryLoad as $factoryId => $days) {
            $planningDailyLimit = $this->planningDailyLimitRepository->findPlanningDailyLimitByFactoryId($factoryId);
            if (null === $planningDailyLimit) {
                continue;
            }
            foreach ($days as $day => $value) {
                if ($value <= 0) {
                    continue;
                }
                $count = $this->equipmentShippingRecordLineRepository->countEquipmentShippingRecordLineWithSameFactoryAndEstimatedPickUpDate($planningDailyLimit->factory->getId(), $day)[0]['count'];
                if (($count + $value) > $planningDailyLimit->days) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('{{ factory }}', $planningDailyLimit->factory->getName())
                        ->setParameter('{{ limit }}', (string) $planningDailyLimit->days)
                        ->setParameter('{{ date }}', $day)
                        ->setParameter('{{ count }}', (string) $count)
                        ->setParameter('{{ value }}', (string) $value)
                        ->atPath($constraint->errorPath)
                        ->addViolation();

                    return;
                }
            }
        }
    }
}
