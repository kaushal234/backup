<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\Finance\AccountReceivable\AccountReceivableImportFileController;
use App\Entity\Country;
use App\Entity\Sales\CustomerErpReference;
use App\Entity\Sales\Order;
use App\Filter\ColumnsFilter;
use App\Filter\Finance\ConvertToEuroRangeFilter;
use App\Serializer\Filter\ContextFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Finance\AccountReceivableRepository')]
#[UniqueEntity(fields: ['erpInvoiceNumber', 'customerErpReference'], message: 'An account receivable already exist for this invoice number on this pcust/erp.', errorPath: 'erpInvoiceNumber')]
#[ApiResource(
    operations: [
        new GetCollection(formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']]),
        new Post(
            uriTemplate: '/account_receivables/import_file/{ssoId}',
            requirements: ['id' => '.+'],
            controller: AccountReceivableImportFileController::class,
            security: "is_granted('FEATURE_ACCOUNT_RECEIVABLES_WRITE')",
            read: false,
            deserialize: false,
            validate: false,
            name: 'import_account_receivables_file',
        ),
        new Get(
            normalizationContext: ['groups' => ['account_receivable', 'account_receivable:detail', 'customer_erp_reference', 'location_public', 'country_list', 'currency', 'transaction_type', 'transaction_type_reference', 'expose_legacy', 'customer', 'people_public', 'sales_order:light', 'subdivision:light', 'customer:watch']],
            security: "is_granted('ACCOUNT_RECEIVABLES_VIEW_VOTER', object)",
        ),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['account_receivable', 'customer_erp_reference', 'location_public', 'country_list', 'currency', 'transaction_type', 'transaction_type_reference', 'expose_legacy', 'customer', 'people_public', 'subdivision:light']],
    denormalizationContext: ['groups' => ['account_receivable:write']],
)]
#[ORM\Table(name: 'account_receivables')]
#[ORM\UniqueConstraint(name: 'unique_record_per_invoice_per_cuno_erp', columns: ['erp_invoice_number', 'customer_erp_reference_id'])]
#[ApiFilter(BooleanFilter::class)]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(ConvertToEuroRangeFilter::class, properties: ['balanceAmount'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'dueDate', 'balanceAmount'])]
#[ApiFilter(DateFilter::class, properties: ['dueDate'])]
#[ApiFilter(SearchFilter::class, properties: ['customerErpReference.customer' => 'exact', 'customerErpReference.customer.mainSalesRepresentative.asm' => 'exact', 'customerErpReference.customer.mainSalesRepresentative.asm.supervisor' => 'exact', 'customerErpReference.customer.type' => 'exact', 'customerErpReference.customer.customerTypes' => 'exact', 'customerErpReference.sso' => 'exact', 'country' => 'exact', 'currency' => 'exact', 'transactionTypeReference.transactionType' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => ['invoice_record']])]
#[ApiFilter(ColumnsFilter::class)]
class AccountReceivable
{
    /**
     * @var int
     */
    final public const DELINQUENT_MINIMUM_WITH_PAST_DUE = 20000;

    /**
     * @var int
     */
    final public const DELINQUENT_MINIMUM = 100000;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\CustomerErpReference')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[MaxDepth(1)]
    #[Assert\NotNull]
    public ?CustomerErpReference $customerErpReference = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\TransactionTypeReference')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public ?TransactionTypeReference $transactionTypeReference = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public string $erpInvoiceNumber;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Country')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public Country $country;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    public ?string $purchaseOrderNumber = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public \DateTimeInterface $invoiceDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public ?\DateTimeInterface $dueDate = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public Currency $currency;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Order')]
    #[Groups(['account_receivable:detail', 'account_receivable:write'])]
    #[MaxDepth(1)]
    public ?Order $order = null;

    #[ORM\Column(type: 'float')]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public float $originalAmount;

    #[ORM\Column(type: 'float')]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public float $originalAmountLocalCurrency;

    #[ORM\Column(type: 'float')]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public float $balanceAmount;

    #[ORM\Column(type: 'float')]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    #[Assert\NotNull]
    public ?float $balanceAmountLocalCurrency = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    public ?int $salesOrderNumber = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    public ?string $salesReferenceA = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    public ?string $salesReferenceB = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    public ?string $financeReferenceA = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    public ?string $financeReferenceB = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    public ?int $creditAnalyst = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['account_receivable', 'account_receivable:write'])]
    public ?\DateTimeInterface $salesOrderDate = null;

    #[ORM\OneToOne(targetEntity: 'App\Entity\Finance\InvoiceRecord', fetch: 'EXTRA_LAZY')]
    #[Groups(['invoice_record'])]
    #[MaxDepth(1)]
    public ?InvoiceRecord $invoiceRecord = null;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type(type: 'boolean')]
    #[Groups(['account_receivable'])]
    public bool $delinquent = false;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['account_receivable'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
