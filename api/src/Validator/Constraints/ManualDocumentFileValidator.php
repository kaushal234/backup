<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualDocument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ManualDocumentFileValidator extends ConstraintValidator
{
    final public const VALID_MIME_TYPES = [
        'MANUAL SECTION' => 'application/pdf',
        ManualDocument::MANUAL_SECTION => 'application/pdf',
        ManualDocument::PARTS_DIAGRAM => 'image/jpeg',
    ];

    public function validate($value, Constraint $constraint): void
    {
        if (\in_array(Manual::NONCRITICAL_VALIDATION_GROUP, $constraint->groups, true)) {
            if (ManualDocument::MANUAL_SECTION === $value->getManualDocument()->type
                && self::getValidMimeTypeForType($value->getManualDocument()->type) !== $value->getMimeType()
            ) {
                $this->context
                    ->buildViolation(\sprintf('The file associated with the part number %s revision %s is not a PDF file', mb_trim((string) $value->getManualDocument()->factoryNumber), mb_trim((string) $value->getManualDocument()->revision)))
                    ->addViolation();
            }

            if (ManualDocument::PARTS_DIAGRAM === $value->getManualDocument()->type
                && ($maxSize = self::getSizeForType($value->getManualDocument()->type)) && $maxSize < $value->getSize()
            ) {
                $this->context
                    ->buildViolation(\sprintf('The size of the JPG associated with the part number %s revision %s must not exceed 1M', mb_trim((string) $value->getManualDocument()->factoryNumber), mb_trim((string) $value->getManualDocument()->revision)))
                    ->addViolation();
            }
        }

        if (\in_array(Manual::CRITICAL_VALIDATION_GROUP, $constraint->groups, true) && (ManualDocument::PARTS_DIAGRAM === $value->getManualDocument()->type
            && self::getValidMimeTypeForType($value->getManualDocument()->type) !== $value->getMimeType())) {
            $this->context
                ->buildViolation(\sprintf('The file associated with the part number %s revision %s is not a JPG file', mb_trim((string) $value->getManualDocument()->factoryNumber), mb_trim((string) $value->getManualDocument()->revision)))
                ->addViolation()
            ;
        }
    }

    public static function getValidMimeTypeForType(string $type): ?string
    {
        return self::VALID_MIME_TYPES[$type] ?? null;
    }

    public static function getSizeForType(string $type, string $unit = 'KB'): ?int
    {
        $maxSize = 1000000;

        if (ManualDocument::PARTS_DIAGRAM === $type) {
            switch ($unit) {
                case 'B' :
                    return $maxSize * 1000;
                case 'MB' :
                    return $maxSize / 1000;
                default:
                    return $maxSize;
            }
        }

        return null;
    }
}
