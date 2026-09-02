<?php

declare(strict_types=1);

namespace App\Tests\AI\Mapper\Legal;

use App\AI\Dto\Legal\ContractParty;
use App\AI\Mapper\Legal\ContractAnalysisMapper;
use PHPUnit\Framework\TestCase;

final class ContractAnalysisMapperTest extends TestCase
{
    public function testItMapsAllFields(): void
    {
        $parsed = [
            'contract_title' => 'Software Licensing Agreement',
            'contract_summary' => 'A summary',
            'contract_start_date' => '2025-01-01',
            'contract_expiration_date' => '2027-01-01',
            'jurisdiction' => 'State of New York, USA',
            'contract_value' => 50000,
            'contract_currency' => 'USD',
            'renewal_period' => 1,
            'renewal_unit' => 'years',
            'parties' => [
                ['party_name' => 'TLD Corp', 'party_role' => 'internal'],
                ['party_name' => 'Air France', 'party_role' => 'client'],
            ],
        ];

        $result = (new ContractAnalysisMapper())->map($parsed);

        self::assertSame('Software Licensing Agreement', $result->shortDescription);
        self::assertSame('A summary', $result->description);
        self::assertSame('2025-01-01', $result->startDate);
        self::assertSame('2027-01-01', $result->expirationDate);
        self::assertSame('State of New York, USA', $result->jurisdiction);
        self::assertSame(50000, $result->value);
        self::assertSame('USD', $result->currency);
        self::assertSame(1, $result->renewalPeriod);
        self::assertSame('years', $result->renewalUnit);
        self::assertSame('File analyzed successfully', $result->message);

        self::assertCount(2, $result->parties);
        self::assertInstanceOf(ContractParty::class, $result->parties[0]);
        self::assertSame('TLD Corp', $result->parties[0]->partyName);
        self::assertSame('internal', $result->parties[0]->partyRole);
        self::assertSame('Air France', $result->parties[1]->partyName);
        self::assertSame('client', $result->parties[1]->partyRole);
    }

    public function testItReturnsNullsWhenFieldsAreMissing(): void
    {
        $result = (new ContractAnalysisMapper())->map([]);

        self::assertNull($result->shortDescription);
        self::assertNull($result->description);
        self::assertNull($result->startDate);
        self::assertNull($result->expirationDate);
        self::assertNull($result->jurisdiction);
        self::assertNull($result->value);
        self::assertNull($result->currency);
        self::assertNull($result->renewalPeriod);
        self::assertNull($result->renewalUnit);
        self::assertSame([], $result->parties);
    }

    public function testItCastsContractValueDecimalToInt(): void
    {
        $result = (new ContractAnalysisMapper())->map(['contract_value' => 1234.99]);

        self::assertSame(1234, $result->value);
    }

    public function testItDefaultsPartyRoleToUnknownWhenMissing(): void
    {
        $result = (new ContractAnalysisMapper())->map([
            'parties' => [['party_name' => 'Acme']],
        ]);

        self::assertCount(1, $result->parties);
        self::assertSame('unknown', $result->parties[0]->partyRole);
    }

    public function testItFiltersOutPartiesWithEmptyOrMissingName(): void
    {
        $result = (new ContractAnalysisMapper())->map([
            'parties' => [
                ['party_name' => 'Acme', 'party_role' => 'client'],
                ['party_name' => '', 'party_role' => 'client'],
                ['party_name' => '   ', 'party_role' => 'client'],
                ['party_role' => 'client'],
                'not-an-array',
                ['party_name' => 'Beta', 'party_role' => 'supplier'],
            ],
        ]);

        self::assertCount(2, $result->parties);
        self::assertSame('Acme', $result->parties[0]->partyName);
        self::assertSame('Beta', $result->parties[1]->partyName);
    }

    public function testItTrimsPartyName(): void
    {
        $result = (new ContractAnalysisMapper())->map([
            'parties' => [['party_name' => '  Acme  ', 'party_role' => 'client']],
        ]);

        self::assertSame('Acme', $result->parties[0]->partyName);
    }

    public function testItReturnsEmptyArrayWhenPartiesIsNotAnArray(): void
    {
        $result = (new ContractAnalysisMapper())->map(['parties' => 'invalid']);

        self::assertSame([], $result->parties);
    }
}
