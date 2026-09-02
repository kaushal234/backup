<?php

declare(strict_types=1);

namespace App\Twig\UserGuide;

enum UserGuide: string
{
    case HOME = '/';
    case VENDOR_WARRANTY_CLAIM = '/vendor-warranty-claim/';
    case SUPPLIER_CORRECTIVE_ACTION_REQUEST = '/supplier-corrective-action-request/';

    private const BASE_TRANSLATION = 'header.menu.user_guide.';

    private const HOME_DMS_ID = [
        1781 => [
            'name' => self::BASE_TRANSLATION.'evendor_help',
            'filename' => 'DMS#1886.pdf',
        ],
    ];

    private const VENDOR_WARRANTY_CLAIM_DMS_ID = [
        6341 => [
            'name' => self::BASE_TRANSLATION.'vendor_waranty_claim_help',
            'filename' => 'DMS#6508.pdf',
        ],
    ];

    private const SUPPLIER_CORRECTIVE_ACTION_REQUEST_DMS_ID = [
        1287 => [
            'name' => self::BASE_TRANSLATION.'supplier_corrective_action_request_help',
            'filename' => 'DMS#1374_SCAR_Evendor_user_guide.pdf',
        ],
        555 => [
            'name' => self::BASE_TRANSLATION.'8d_format',
            'filename' => 'DMS#604_Corrective_Preventive_Action_Request.xlsx',
        ],
    ];

    /**
     * @return array<int, array<string, string>>
     */
    public static function getMapping(string $pathInfo): array
    {
        return match ($pathInfo) {
            self::HOME->value => self::HOME_DMS_ID,
            self::VENDOR_WARRANTY_CLAIM->value => self::VENDOR_WARRANTY_CLAIM_DMS_ID,
            self::SUPPLIER_CORRECTIVE_ACTION_REQUEST->value => self::SUPPLIER_CORRECTIVE_ACTION_REQUEST_DMS_ID,
            default => [],
        };
    }
}
