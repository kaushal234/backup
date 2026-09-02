<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Entity\Sales\CustomerErpReference;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[UniqueEntity(fields: ['invoiceNumber', 'customerErpReference'], message: 'A record already exist for this invoice.', errorPath: 'invoiceNumber')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            denormalizationContext: ['groups' => ['invoice_record:create']],
            securityPostDenormalize: "is_granted('INVOICE_RECORD_WRITE_VOTER', object)"
        ),
        new Get(security: "is_granted('ACCOUNT_RECEIVABLES_VIEW_VOTER', object)"),
        new Put(
            denormalizationContext: ['groups' => ['invoice_record:edit']],
            security: "is_granted('INVOICE_RECORD_WRITE_VOTER', object)",
        ),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['invoice_record', 'customer_erp_reference', 'customer_list', 'location_public', 'country_list', 'currency']],
)]
#[ORM\Table(name: 'invoice_records')]
#[ORM\UniqueConstraint(name: 'unique_record_per_invoice', columns: ['invoice_number', 'customer_erp_reference_id'])]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[ApiFilter(SearchFilter::class, properties: ['customerErpReference', 'invoiceNumber', 'category', 'customerErpReference.customer', 'customerErpReference.sso'])]
#[App\Loggable]
class InvoiceRecord
{
    /**
     * @var string
     */
    final public const TECHNICAL_PROBLEM = 'TECHNICAL PROBLEM';

    /**
     * @var string
     */
    final public const INSOLVENCY = 'INSOLVENCY';

    /**
     * @var string
     */
    final public const ADMINISTRATIVE_PROBLEM = 'ADMINISTRATIVE PROBLEM';

    /**
     * @var string
     */
    final public const SERIOUS_DISPUTE = 'SERIOUS DISPUTE';

    /**
     * @var string
     */
    final public const PAYMENT_DEFERRAL = 'PAYMENT DEFERRAL';

    /**
     * @var string
     */
    final public const DISPUTE = 'DISPUTE';

    /**
     * @var string
     */
    final public const OTHER = 'OTHER';

    #[ORM\Column(type: 'float')]
    #[Groups(['invoice_record', 'invoice_record:create'])]
    #[Assert\NotNull]
    public float $originalAmount;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\Currency')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['invoice_record', 'invoice_record:create'])]
    public Currency $currency;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\CustomerErpReference')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['invoice_record', 'invoice_record:create'])]
    public CustomerErpReference $customerErpReference;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::TECHNICAL_PROBLEM, self::ADMINISTRATIVE_PROBLEM, self::SERIOUS_DISPUTE, self::PAYMENT_DEFERRAL, self::DISPUTE, self::OTHER, self::INSOLVENCY])]
    #[Groups(['invoice_record', 'invoice_record:create', 'invoice_record:edit'])]
    public ?string $category = null;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Groups(['invoice_record', 'invoice_record:create'])]
    public string $invoiceNumber;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['invoice_record', 'invoice_record:create', 'invoice_record:edit'])]
    public ?\DateTimeInterface $expectedPaymentDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['invoice_record', 'invoice_record:write_once'])]
    public ?\DateTimeInterface $revisedDueDate = null;

    #[Groups(['invoice_record:create', 'invoice_record:edit'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['account_receivable:export', 'invoice_record'])]
    #[Exclude]
    public ?string $lastComment = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: 'comment')]
    public ?\DateTimeInterface $lastCommentedAt = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['invoice_record'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ((null === $this->revisedDueDate && null === $this->expectedPaymentDate && null === $this->category)
            || (null !== $this->expectedPaymentDate && null === $this->category && null === $this->revisedDueDate)
            || (null === $this->expectedPaymentDate && null !== $this->category && null === $this->revisedDueDate)) {
            $context->buildViolation('Either expected payment date AND category OR revised due date only should be set.')
                ->atPath('revisedDueDate')
                ->addViolation();
        }
    }
}
