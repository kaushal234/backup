<?php

declare(strict_types=1);

namespace App\ION\ResourceSourceProvider\Procurement;

use App\ION\Resources\Procurement\RequestForQuotation;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;

class RequestForQuotationResourceSourceProvider implements ResourceSourceProviderInterface
{
    public function getItemReadOperation(): ?string
    {
        return 'txShow';
    }

    public function getCollectionReadOperation(): ?string
    {
        return 'txList';
    }

    public function getResource(): ?string
    {
        return 'txRequestForQuotations';
    }

    public function getUpdateOperation(): ?string
    {
        return null;
    }

    public function getCreateOperation(): ?string
    {
        return null;
    }

    public function getDataAreaFilter(): array
    {
        return [];
    }

    public function deserializeAfterPersist(): bool
    {
        return false;
    }

    public function getRestItemReadOperation(array $uriVariables = []): ?string
    {
        return \sprintf("txpur.RequestForQuote/GetRFQ(rfq='%s')", $uriVariables['requestForQuotationCode']);
    }

    public function getRestCollectionReadOperation(array $uriVariables = []): ?string
    {
        return \sprintf("txpur.RequestForQuote/GetRFQs(bidder='%s')", $uriVariables['bidder'] ?? '');
    }

    public function getFilters(): array
    {
        return [];
    }

    public function supports(string $class): bool
    {
        return RequestForQuotation::class === $class;
    }
}
