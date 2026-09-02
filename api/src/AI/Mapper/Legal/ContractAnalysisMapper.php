<?php

declare(strict_types=1);

namespace App\AI\Mapper\Legal;

use App\AI\Dto\Legal\ContractAnalysis;
use App\AI\Dto\Legal\ContractParty;

final readonly class ContractAnalysisMapper
{
    /**
     * @param array<string, mixed> $parsed
     */
    public function map(array $parsed): ContractAnalysis
    {
        return new ContractAnalysis(
            shortDescription: isset($parsed['contract_title']) ? (string) $parsed['contract_title'] : null,
            description: isset($parsed['contract_summary']) ? (string) $parsed['contract_summary'] : null,
            startDate: isset($parsed['contract_start_date']) ? (string) $parsed['contract_start_date'] : null,
            expirationDate: isset($parsed['contract_expiration_date']) ? (string) $parsed['contract_expiration_date'] : null,
            jurisdiction: isset($parsed['jurisdiction']) ? (string) $parsed['jurisdiction'] : null,
            value: isset($parsed['contract_value']) ? (int) $parsed['contract_value'] : null,
            currency: isset($parsed['contract_currency']) ? (string) $parsed['contract_currency'] : null,
            renewalPeriod: isset($parsed['renewal_period']) ? (int) $parsed['renewal_period'] : null,
            renewalUnit: isset($parsed['renewal_unit']) ? (string) $parsed['renewal_unit'] : null,
            parties: $this->mapParties($parsed['parties'] ?? null),
            message: 'File analyzed successfully',
        );
    }

    /**
     * @return ContractParty[]
     */
    private function mapParties(mixed $raw): array
    {
        if (!\is_array($raw)) {
            return [];
        }

        $parties = [];
        foreach ($raw as $party) {
            if (!\is_array($party)) {
                continue;
            }
            $name = mb_trim((string) ($party['party_name'] ?? ''));
            if ('' === $name) {
                continue;
            }
            $parties[] = new ContractParty(
                partyName: $name,
                partyRole: (string) ($party['party_role'] ?? 'unknown'),
            );
        }

        return $parties;
    }
}
