<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;
use Psl\Vec;

enum SupplierCorrectiveActionRequestStatus: string
{
    case Closed = 'CLOSED';
    case Pending = 'PENDING';
    case VendorToFillForm = 'VENDOR TO FILL FORM';
    case TldToFillForm = 'TLD TO REVIEW FORM';
    case Validation = 'VALIDATION';
    case Cancel = 'CANCEL';
    case CommercialAgreement = 'COMMERCIAL AGREEMENT';

    public function presentableValue(): string
    {
        return match ($this) {
            self::Cancel => 'CANCELED',
            default => $this->value,
        };
    }

    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\union(...Vec\map(
            self::cases(),
            /**
             * @return Type\TypeInterface<string>
             */
            static fn (SupplierCorrectiveActionRequestStatus $status): Type\TypeInterface => Type\literal_scalar($status->value),
        ));
    }
}
