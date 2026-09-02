<?php

declare(strict_types=1);

namespace App\ION\Resources\Procurement\Orders\Statistic;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: CachedIONCollectionDataProvider::class),
        new Get(provider: CachedIONItemDataProvider::class),
    ],
    routePrefix: 'ion',
    normalizationContext: ['groups' => ['purchase_order_statistic']],
    security: "is_granted('FEATURE_PURCHASE_ORDER_STATISTIC_VIEW')",
)]
class PurchaseOrderStatistic
{
    #[ApiProperty(identifier: true)]
    #[Groups(['purchase_order_statistic'])]
    public string $buyFromSupplierCode;

    #[Groups(['purchase_order_statistic'])]
    public string $buyFromSupplierName;

    #[Groups(['purchase_order_statistic'])]
    public bool $terminated;

    /**
     * @var Contact[]
     */
    #[Groups(['purchase_order_statistic'])]
    private array $contacts = [];
    /**
     * @var Contact[]
     */
    #[Groups(['purchase_order_statistic'])]
    private array $internalContacts = [];

    /**
     * @var PurchaseOrderLineStatistic[]
     */
    #[Groups(['purchase_order_statistic'])]
    private array $lines = [];

    public function getContacts(): array
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        $this->contacts[] = $contact;

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }

    public function getInternalContacts(): array
    {
        return $this->internalContacts;
    }

    public function addInternalContact(Contact $contact): self
    {
        $this->internalContacts[] = $contact;

        return $this;
    }

    public function removeInternalContact(Contact $contact): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function addLine(PurchaseOrderLineStatistic $line): self
    {
        $this->lines[] = $line;

        return $this;
    }

    public function removeLine(PurchaseOrderLineStatistic $line): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
