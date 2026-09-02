<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\File\ZipController;
use App\ION\DataProvider\CachedCollectionDataProvider;
use App\ION\DataProvider\CachedItemDataProvider;
use App\ION\Resources\MasterData\EnterpriseModel\EnterpriseStructure\Site;
use App\ION\Resources\SiteInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: CachedCollectionDataProvider::class),
        new Get(
            security: "is_granted('ACCESS_PEOPLE') or is_granted('REQUEST_FOR_QUOTATION_VOTER', object)",
            provider: CachedItemDataProvider::class,
        ),
        new Get(
            uriTemplate: '/request_for_quotations/{requestForQuotationCode}/zip',
            formats: ['zip' => 'application/zip'],
            controller: ZipController::class,
            name: 'ion_request_for_quotation_zip',
            provider: CachedItemDataProvider::class,
        ),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['request_for_quotation', 'site']],
    denormalizationContext: [],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_VENDOR_USER')",
)]
class RequestForQuotation implements SiteInterface
{
    #[ApiProperty(identifier: true)]
    #[Groups(['request_for_quotation'])]
    public string $requestForQuotationCode;

    #[Groups(['request_for_quotation'])]
    public string $description;

    #[Groups(['request_for_quotation'])]
    public string $buyerEmail;

    #[Groups(['request_for_quotation'])]
    public string $responseDate;

    #[Groups(['request_for_quotation'])]
    public string $status;

    #[Groups(['request_for_quotation'])]
    public ?Site $site;

    /**
     * @var RequestForQuotationLine[]
     */
    #[Groups(['request_for_quotation'])]
    private array $lines = [];

    /**
     * @var RequestForQuotationBidder[]
     */
    #[Groups(['request_for_quotation'])]
    private array $bidders = [];

    public function getLines(): array
    {
        return $this->lines;
    }

    public function addLine(RequestForQuotationLine $requestForQuotationLine): self
    {
        $this->lines[] = $requestForQuotationLine;

        return $this;
    }

    public function removeLine(RequestForQuotationLine $requestForQuotationLine): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }

    public function getBidders(): array
    {
        return $this->bidders;
    }

    public function addBidder(RequestForQuotationBidder $bidder): self
    {
        $this->bidders[] = $bidder;

        return $this;
    }

    public function removeBidder(RequestForQuotationBidder $requestForQuotationLine): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }

    public function getSiteNumber(): ?int
    {
        return (int) $this->site->siteID;
    }
}
