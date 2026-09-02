<?php

declare(strict_types=1);

namespace App\Factory\Quality;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\EquipmentRecord;
use App\Entity\Parts\CrabPart;
use App\Entity\Quality\Crab;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CrabFactory
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function createCrab(Crab $originalCrab, EquipmentRecord $equipmentRecord)
    {
        $crab = new Crab();
        $part = null;
        if (null !== $originalCrab->getPart()) {
            $part = new CrabPart();
            $part->partNumber = $originalCrab->getPart()->partNumber;
            $part->description = $originalCrab->getPart()->description;
        }
        $crab->setPart($part);

        $crab->equipmentRecord = $equipmentRecord;
        $crab->description = $originalCrab->description;
        $crab->category = $originalCrab->category;
        $crab->department = $originalCrab->department;
        $crab->nonConformity = $originalCrab->nonConformity;
        $crab->code = $originalCrab->code;

        if ($originalCrab->getMainFile()) {
            $crab->setMainFile(clone $originalCrab->getMainFile());
        }

        foreach ($originalCrab->getFiles() as $file) {
            $crab->addFile(clone $file);
        }

        $violations = $this->validator->validate($crab, null, ['Default', 'crab:create']);

        if (\count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return $crab;
    }
}
