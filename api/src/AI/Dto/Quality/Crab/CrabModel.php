<?php

declare(strict_types=1);

namespace App\AI\Dto\Quality\Crab;

use App\AI\Dto\Directory\PeopleModel;
use App\AI\Dto\Parts\CrabPartModel;
use App\AI\Dto\Quality\DerogationModel;
use App\AI\Dto\Support\EquipmentRecordModel;

final readonly class CrabModel
{
    public function __construct(
        public string $status,
        public \DateTimeInterface $createdAt,
        public ?\DateTimeInterface $fixedAt,
        public ?\DateTimeInterface $inspectedAt,
        public string $description,
        public ?string $fixingComments,
        public ?string $inspectingComments,
        public string $category,
        public ?int $eapId,
        public ?int $piQuestionId,
        public ?string $piQuestionType,
        public CrabDepartmentModel $department,
        public ?EquipmentRecordModel $equipmentRecord,
        public ?PeopleModel $createdBy,
        public ?PeopleModel $fixedBy,
        public ?PeopleModel $inspectedBy,
        public ?CrabCodeModel $code,
        public ?int $nonConformity,
        public ?DerogationModel $derogation,
        public ?int $firstArticleQualification,
        public ?CrabPartModel $part,
    ) {
    }
}
