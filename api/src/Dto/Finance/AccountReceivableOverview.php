<?php

declare(strict_types=1);

namespace App\Dto\Finance;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\DataProvider\AccountReceivableOverviewDataProvider;
use App\Entity\Finance\TransactionTypeReference;
use App\Entity\Sales\CustomerErpReference;
use App\Filter\Finance\AccountReceivableOverviewFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            security: "is_granted('FEATURE_ACCOUNT_RECEIVABLES_VIEW_FULL') or is_granted('FEATURE_ACCOUNT_RECEIVABLES_VIEW_SSO')",
            provider: AccountReceivableOverviewDataProvider::class,
        ),
        new Get(),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['account_receivable:overview', 'customer_erp_reference', 'people_public', 'customer_list', 'location_public']]
)]
#[ApiFilter(AccountReceivableOverviewFilter::class)]
class AccountReceivableOverview
{
    public ?TransactionTypeReference $transactionTypeReference = null;

    #[Groups(['account_receivable:overview'])]
    public string $currency;

    #[Groups(['account_receivable:overview'])]
    public ?CustomerErpReference $customerErpReference = null;

    #[Groups(['account_receivable:overview'])]
    public float $totalValue;

    #[Groups(['account_receivable:overview'])]
    public float $notPastDue;

    #[Groups(['account_receivable:overview'])]
    public float $pastDueOneMonth;

    #[Groups(['account_receivable:overview'])]
    public float $pastDueTwoMonths;

    #[Groups(['account_receivable:overview'])]
    public float $pastDueThreeMonths;

    #[Groups(['account_receivable:overview'])]
    public float $pastDueSixMonths;

    #[Groups(['account_receivable:overview'])]
    public float $pastDueMoreThanSixMonths;

    #[Groups(['account_receivable:overview'])]
    public int $pastDuePercentage;

    #[Groups(['account_receivable:overview'])]
    public int $pastDueTwoMonthsPercentage;

    #[ApiProperty(identifier: true)]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
