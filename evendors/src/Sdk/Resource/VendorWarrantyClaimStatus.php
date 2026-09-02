<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

use Psl\Type;
use Psl\Vec;

enum VendorWarrantyClaimStatus: string
{
    case Pending = 'PENDING';
    case QAAnalysis = 'QA ANALYSIS';
    case VendorToRespond = 'VENDOR_TO_RESPOND';
    case ReviewVendorResponse = 'REVIEW_VENDOR_RESPONSE';
    case CreatePO = 'CREATE_PO';
    case ShipToVendor = 'SHIP_TO_VENDOR';
    case IssueCreditNote = 'ISSUE_CREDIT_NOTE';
    case RecFromVendor = 'REC_FROM_VENDOR';
    case IssueDebitNote = 'ISSUE_DEBIT_NOTE';
    case ValidateSCAR = 'VALIDATE_SCAR';
    case ClosedResolved = 'CLOSED_RESOLVED';
    case ClosedLowValue = 'CLOSED_LOW_VALUE';
    case ClosedVendorRejected = 'CLOSED_VENDOR_REJECTED';
    case ClosedNotVendorIssue = 'CLOSED_NOT_VENDOR_ISSUE';

    /**
     * @return Type\TypeInterface<non-empty-string>
     */
    public static function getTypeStructure(): Type\TypeInterface
    {
        return Type\union(...Vec\map(
            self::cases(),
            /**
             * @return Type\TypeInterface<non-empty-string>
             */
            static fn (VendorWarrantyClaimStatus $status): Type\TypeInterface => Type\literal_scalar($status->value),
        ));
    }
}
