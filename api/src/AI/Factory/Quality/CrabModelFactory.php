<?php

declare(strict_types=1);

namespace App\AI\Factory\Quality;

use App\AI\Dto\Parts\CrabPartModel;
use App\AI\Dto\Quality\Crab\CrabCodeModel;
use App\AI\Dto\Quality\Crab\CrabDepartmentModel;
use App\AI\Dto\Quality\Crab\CrabModel;
use App\AI\Dto\Quality\DerogationModel;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Factory\Support\EquipmentRecordModelFactory;
use App\Entity\Quality\Crab;
use App\Entity\Quality\CrabCode;
use App\Entity\Quality\CrabDepartment;
use App\Entity\Quality\Derogation;

final readonly class CrabModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private EquipmentRecordModelFactory $equipmentRecordModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return Crab::class === $class;
    }

    /**
     * @param Crab $entity
     */
    public function create(object $entity): CrabModel
    {
        $part = $entity->getPart();

        return new CrabModel(
            status: $entity->status,
            createdAt: $entity->createdAt,
            fixedAt: $entity->fixedAt,
            inspectedAt: $entity->inspectedAt,
            description: $entity->description,
            fixingComments: $entity->fixingComments,
            inspectingComments: $entity->inspectingComments,
            category: $entity->category,
            eapId: $entity->eapId,
            piQuestionId: $entity->piQuestionId,
            piQuestionType: $entity->piQuestionType,
            department: $this->createDepartment($entity->department),
            equipmentRecord: null === $entity->equipmentRecord ? null : $this->equipmentRecordModelFactory->create($entity->equipmentRecord),
            createdBy: null === $entity->createdBy ? null : $this->peopleModelFactory->create($entity->createdBy),
            fixedBy: null === $entity->fixedBy ? null : $this->peopleModelFactory->create($entity->fixedBy),
            inspectedBy: null === $entity->inspectedBy ? null : $this->peopleModelFactory->create($entity->inspectedBy),
            code: null === $entity->code ? null : $this->createCode($entity->code),
            nonConformity: $entity->nonConformity?->getId(),
            derogation: null === $entity->derogation ? null : $this->createDerogation($entity->derogation),
            firstArticleQualification: $entity->firstArticleQualification?->getId(),
            part: null === $part ? null : new CrabPartModel(
                createdAt: $part->createdAt,
                partNumber: $part->partNumber,
                description: $part->description,
                quantity: $part->quantity,
                unitOfMeasure: $part->unitOfMeasure,
            ),
        );
    }

    private function createDepartment(CrabDepartment $department): CrabDepartmentModel
    {
        return new CrabDepartmentModel(name: $department->name);
    }

    private function createCode(CrabCode $code): CrabCodeModel
    {
        return new CrabCodeModel(
            code: $code->code,
            description: $code->description,
        );
    }

    private function createDerogation(Derogation $derogation): DerogationModel
    {
        return new DerogationModel(
            status: $derogation->getStatus(),
            shortDescription: $derogation->shortDescription,
            description: $derogation->description,
            dueDate: $derogation->dueDate,
            closedAt: $derogation->closedAt,
            assignor: $this->peopleModelFactory->create($derogation->assignor),
            assignee: null === $derogation->assignee ? null : $this->peopleModelFactory->create($derogation->assignee),
            closedBy: null === $derogation->closedBy ? null : $this->peopleModelFactory->create($derogation->closedBy),
        );
    }
}
