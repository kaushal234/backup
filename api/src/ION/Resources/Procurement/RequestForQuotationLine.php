<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement;

use App\ION\Resources\MasterData\EnterpriseModel\EnterpriseStructure\Site;
use App\ION\Resources\RevisionDateInterface;
use Symfony\Component\Serializer\Attribute\Groups;

class RequestForQuotationLine implements RevisionDateInterface
{
    #[Groups(['request_for_quotation'])]
    public int $position;

    #[Groups(['request_for_quotation'])]
    public int $sequence;

    #[Groups(['request_for_quotation'])]
    public string $item;

    #[Groups(['request_for_quotation'])]
    public string $itemDescription;

    #[Groups(['request_for_quotation'])]
    public string $itemRevision;

    #[Groups(['request_for_quotation'])]
    public string $itemSignalCode;

    #[Groups(['request_for_quotation'])]
    public string $date;

    #[Groups(['request_for_quotation'])]
    public float $quantity;

    #[Groups(['request_for_quotation'])]
    public string $unitOfMeasure;

    #[Groups(['request_for_quotation'])]
    public string $warehouse;

    #[Groups(['request_for_quotation'])]
    public string $receiptDate;

    #[Groups(['request_for_quotation', 'site'])]
    public ?Site $site;

    public function getDate(): ?string
    {
        return $this->date;
    }
}
