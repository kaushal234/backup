<?php

declare(strict_types=1);

namespace App\Entity\Common;

enum SubscriptionResourceModule: string
{
    case Demo = 'DEMO';
    case Task = 'TASK';
    case Meeting = 'MOM';
    case SalesForecast = 'SFR';
    case TroubleTicket = 'TTS';
    case Scar = 'SCAR';
    case Contract = 'CRS';
    case VendorWarrantyClaim = 'VWC';

    /**
     * @return list<string>
     */
    public function iriPrefixes(): array
    {
        return match ($this) {
            self::Demo => ['/sales/demos/'],
            self::Task => ['/tasks/'],
            self::Meeting => ['/minutes_of_meeting/meetings/'],
            self::SalesForecast => ['/sales/sales_forecasts/'],
            self::TroubleTicket => ['/mis/trouble_tickets/'],
            self::Scar => ['/quality/supplier_corrective_action_requests/'],
            self::Contract => ['/contracts/'],
            self::VendorWarrantyClaim => [
                '/purchasing/vendor_warranty_claims/',
                '/purchasing/ncr_vendor_warranty_claims/',
                '/purchasing/wc_vendor_warranty_claims/',
            ],
        };
    }

    public static function fromResourceIri(string $iri): ?self
    {
        foreach (self::cases() as $case) {
            foreach ($case->iriPrefixes() as $prefix) {
                if (str_starts_with($iri, $prefix)) {
                    return $case;
                }
            }
        }

        return null;
    }
}
