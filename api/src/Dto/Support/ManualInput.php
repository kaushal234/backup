<?php

declare(strict_types=1);

namespace App\Dto\Support;

use App\Entity\EquipmentRecord;
use App\ION\Validator\Constraints\MasterData\LogisticCodes\Language;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\GroupSequence(['ManualInput', 'EquipmentRecord'])]
class ManualInput
{
    /** @var string */
    final public const REQUEST_PARAMETER_NAME = 'input_data';

    #[Assert\NotNull]
    public EquipmentRecord $mainEquipmentRecord;

    #[Language]
    public string $language = 'en';

    #[Assert\Choice(choices: ['RELEASED', 'PRELIMINARY'])]
    public string $status = 'RELEASED';

    public bool $force = false;

    /**
     * @var EquipmentRecord[]
     */
    private array $secondaryEquipmentRecords = [];

    public function getSecondaryEquipmentRecords(): array
    {
        return $this->secondaryEquipmentRecords;
    }

    public function setSecondaryEquipmentRecords(array $secondaryEquipmentRecords): self
    {
        $this->secondaryEquipmentRecords = $secondaryEquipmentRecords;

        return $this;
    }

    #[Assert\Callback(groups: ['EquipmentRecord'])]
    public function validateCritical(ExecutionContextInterface $context): void
    {
        if (null === $this->mainEquipmentRecord->getProjectNumber()) {
            $context
                ->buildViolation('Equipment project number is missing')
                ->addViolation()
            ;
        }
    }
}
