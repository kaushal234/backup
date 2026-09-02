<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use App\Sdk\Resource\Traits\CompleteTypeStructureTrait;
use Psl\Type;

class TechnicianOnCall implements ResourceInterface
{
    use CompleteTypeStructureTrait;

    /**
     * @param list<Tag>  $tags
     * @param list<File> $files
     */
    public function __construct(
        public readonly string $iri,
        public readonly int $id,
        public readonly bool $confidential,
        public readonly string $title,
        public readonly string $description,
        public readonly string $status,
        public readonly \DateTime $createdAt,
        public readonly TechnicianOnCallType $technicianOnCallType,
        public readonly ServiceActivity $serviceActivity,
        public readonly string $indiceFactor,
        public readonly Airport $airport,
        public readonly Location $salesOrganisationService,
        public readonly bool $factoryFlag,
        public readonly Customer $customer,
        public readonly int $openDays,
        public readonly int $daysWithoutActivity,
        public readonly string $daysWithoutActivityStatus,
        public readonly ?string $originalTitle,
        public readonly ?string $originalDescription,
        public readonly ?\DateTime $updatedAt = null,
        public readonly ?People $createdBy = null,
        public readonly ?EquipmentRecord $equipmentRecord = null,
        public readonly ?People $assignee = null,
        public readonly ?UnitOperationalStatus $unitOperationalStatus = null,
        public readonly ?User $mainContact = null,
        public readonly ?TechnicianOnCallSurvey $survey = null,
        public readonly ?string $token = null,
        public readonly ?File $mainFile = null,
        public readonly array $files = [],
        public readonly array $tags = [],
    ) {
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'confidential' => Type\bool(),
            'title' => Type\non_empty_string(),
            //            'originalTitle' => Type\nullable(Type\non_empty_string()),
            'description' => Type\non_empty_string(),
            //            'originalDescription' => Type\nullable(Type\non_empty_string()),
            'status' => Type\non_empty_string(),
            'createdAt' => Type\non_empty_string(),
            'updatedAt' => Type\nullable(Type\non_empty_string()),
            'createdBy' => Type\nullable(People::getTypeStructure()),
            'equipmentRecord' => Type\nullable(EquipmentRecord::getEmbedStructure()),
            'assignee' => Type\nullable(People::getTypeStructure()),
            'unitOperationalStatus' => Type\nullable(UnitOperationalStatus::getTypeStructure()),
            'technicianOnCallType' => TechnicianOnCallType::getTypeStructure(),
            'serviceActivity' => ServiceActivity::getTypeStructure(),
            'indiceFactor' => Type\non_empty_string(),
            'airport' => Airport::getTypeStructure(),
            'salesOrganisationService' => Location::getTypeStructure(),
            'factoryFlag' => Type\bool(),
            'customer' => Customer::getTypeStructure(),
            'mainContact' => Type\nullable(User::getTypeStructure()),
            'mainFile' => Type\nullable(File::getTypeStructure()),
            'openDays' => Type\int(),
            'daysWithoutActivity' => Type\int(),
            'daysWithoutActivityStatus' => Type\non_empty_string(),
            'survey' => Type\nullable(TechnicianOnCallSurvey::getTypeStructure()),
            'token' => Type\nullable(Type\non_empty_string()),
        ], allowUnknownFields: true);
    }

    public static function getPageTypeStructure(): Type\TypeInterface
    {
        return self::getPageTypeStructureOf(Type\shape([
            '@id' => Type\non_empty_string(),
            'id' => Type\int(),
            'confidential' => Type\bool(),
            'title' => Type\non_empty_string(),
            //            'originalTitle' => Type\nullable(Type\non_empty_string()),
            'description' => Type\non_empty_string(),
            //            'originalDescription' => Type\nullable(Type\non_empty_string()),
            'status' => Type\non_empty_string(),
            'createdAt' => Type\non_empty_string(),
            'updatedAt' => Type\nullable(Type\non_empty_string()),
            'createdBy' => Type\nullable(People::getTypeStructure()),
            'equipmentRecord' => Type\nullable(EquipmentRecord::getEmbedStructure()),
            'assignee' => Type\nullable(People::getTypeStructure()),
            'unitOperationalStatus' => Type\nullable(UnitOperationalStatus::getTypeStructure()),
            'technicianOnCallType' => TechnicianOnCallType::getTypeStructure(),
            'serviceActivity' => ServiceActivity::getTypeStructure(),
            'indiceFactor' => Type\non_empty_string(),
            'airport' => Airport::getTypeStructure(),
            'salesOrganisationService' => Location::getTypeStructure(),
            'factoryFlag' => Type\bool(),
            'customer' => Customer::getTypeStructure(),
            'mainContact' => Type\nullable(User::getTypeStructure()),
            'mainFile' => Type\nullable(File::getTypeStructure()),
            'openDays' => Type\int(),
            'daysWithoutActivity' => Type\int(),
            'daysWithoutActivityStatus' => Type\non_empty_string(),
        ], allowUnknownFields: true));
    }
}
