<?php

declare(strict_types=1);

namespace App\Tests\Entity\Parts;

use App\Entity\Parts\SparePartsRequestPart;
use App\Entity\Parts\TOCSparePartsRequest;
use PHPUnit\Framework\TestCase;

class SparePartsRequestTest extends TestCase
{
    public function testImportPartsFromSparePartsRequest()
    {
        $sparePartsRequest = new TOCSparePartsRequest();
        $sparePartsRequest->addPart($this->createPart('AB1000', 'AB', 11.5));
        $sparePartsRequest->addPart($this->createPart('CD2000', 'CD', 5));
        $sparePartsRequest->addPart($this->createPart('YZ9000', 'YZ', 7, true));

        $otherSparePartsRequest = new TOCSparePartsRequest();
        $otherSparePartsRequest->addPart($this->createPart('AB1000', 'Same part number than the part of the first SPR', 8.5));
        $otherSparePartsRequest->addPart($this->createPart('EF3000', 'EF', 5));
        $otherSparePartsRequest->addPart($this->createPart('GH4000', 'Deleted part, should still be added', 6, true));
        $otherSparePartsRequest->addPart($this->createPart('CD2000', 'Deleted part with same PN than a non deleted part, should be added as a new part, no need to combine', 2, true));
        $otherSparePartsRequest->addPart($this->createPart('YZ9000', 'Deleted part with same PN than another deleted part', 2, true));

        $sparePartsRequest->importPartsFromSparePartsRequest($otherSparePartsRequest);

        /** @var SparePartsRequestPart[] $parts */
        $parts = $sparePartsRequest->getAllParts()->toArray();
        $this->assertCount(6, $parts);
        $this->assertSame($parts[0]->partNumber, 'AB1000');
        $this->assertSame($parts[0]->quantity, 20.0);
        $this->assertSame($parts[1]->partNumber, 'CD2000');
        $this->assertSame($parts[1]->quantity, 5.0);
        $this->assertSame($parts[2]->partNumber, 'YZ9000');
        $this->assertSame($parts[2]->quantity, 9.0);
        $this->assertSame($parts[3]->partNumber, 'EF3000');
        $this->assertSame($parts[3]->quantity, 5.0);
        $this->assertSame($parts[4]->partNumber, 'GH4000');
        $this->assertSame($parts[4]->quantity, 6.0);
        $this->assertSame($parts[5]->partNumber, 'CD2000');
        $this->assertSame($parts[5]->quantity, 2.0);
    }

    private function createPart(string $partNumber, string $description, float $quantity, bool $deleted = false): SparePartsRequestPart
    {
        $part = new SparePartsRequestPart();
        $part->partNumber = $partNumber;
        $part->description = $description;
        $part->quantity = $quantity;

        if ($deleted) {
            $part->deletedAt = new \DateTimeImmutable();
        }

        return $part;
    }
}
