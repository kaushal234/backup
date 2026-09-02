<?php

declare(strict_types=1);

namespace App\Entity\Quality\FirstArticleQualification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\People;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[ApiResource(
    shortName: 'firstArticleQualificationsPlanItem',
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['faq_plan_item', 'people_public']]),
        new Get(),
        new Put(
            normalizationContext: ['groups' => ['faq_item_type_detail', 'faq_plan_item_detail']],
            denormalizationContext: ['groups' => ['faq_complete_plan_write']],
        ),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['faq_plan_item_detail', 'people_public']],
    denormalizationContext: ['groups' => ['faq_plan_item_write']],
    forceEager: false,
)]
#[ORM\Table(name: 'first_article_qualifications_plan_items')]
#[App\Loggable(owner: 'firstArticleQualification', ownerRelation: 'plan', showIri: true)]
class PlanItem implements \Stringable
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['faq_plan_item', 'faq_plan_item_detail'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\FirstArticleQualification\PlanItemType')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['faq_plan_item', 'faq_detail', 'faq_plan_item_detail', 'faq_plan_item_write'])]
    private PlanItemType $type;

    #[ORM\Column(type: 'text')]
    #[Assert\NotNull]
    #[Groups(['faq_plan_item', 'faq_plan_item_detail', 'faq_plan_item_write'])]
    private ?string $description = '';

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['faq_plan_item', 'faq_plan_item_detail', 'faq_plan_item_write', 'faq_complete_plan_write'])]
    private int $completionRate = 0;

    #[ORM\Column(type: 'text', options: ['default' => ''])]
    #[Groups(['faq_plan_item', 'faq_plan_item_detail', 'faq_plan_item_write'])]
    private string $comment = '';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\FirstArticleQualification\FirstArticleQualification', inversedBy: 'plan')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['faq_plan_item_write'])]
    private FirstArticleQualification $firstArticleQualification;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['faq_plan_item', 'faq_plan_item_detail', 'faq_plan_item_write'])]
    private ?bool $requestedPriorDelivery = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['faq_plan_item', 'faq_plan_item_detail', 'faq_plan_item_write'])]
    private ?bool $requestedAtPurchaseOrder = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['faq_plan_item_detail'])]
    private ?\DateTimeInterface $validatedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['faq_plan_item_detail', 'people_public'])]
    private ?People $validatedBy = null;

    public function __toString(): string
    {
        $string = $this->getType()->getDescription();
        if ('' !== $this->description) {
            $string .= ' / '.$this->description;
        }
        $string .= ' / '.$this->completionRate.'%';

        return $string;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return PlanItemType
     */
    public function getType()
    {
        return $this->type;
    }

    public function setType(PlanItemType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCompletionRate(): int
    {
        return $this->completionRate;
    }

    public function setCompletionRate(int $completionRate): self
    {
        $this->completionRate = $completionRate;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFirstArticleQualification(): FirstArticleQualification
    {
        return $this->firstArticleQualification;
    }

    public function setFirstArticleQualification(FirstArticleQualification $firstArticleQualification): self
    {
        $this->firstArticleQualification = $firstArticleQualification;

        return $this;
    }

    public function isRequestedPriorDelivery(): ?bool
    {
        return $this->requestedPriorDelivery;
    }

    public function setRequestedPriorDelivery(?bool $requestedPriorDelivery = null): self
    {
        $this->requestedPriorDelivery = $requestedPriorDelivery;

        return $this;
    }

    public function isRequestedAtPurchaseOrder(): ?bool
    {
        return $this->requestedAtPurchaseOrder;
    }

    public function setRequestedAtPurchaseOrder(?bool $requestedAtPurchaseOrder = null): self
    {
        $this->requestedAtPurchaseOrder = $requestedAtPurchaseOrder;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getValidatedBy(): ?People
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(People $validatedBy): self
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->getType()->isRequestablePriorDelivery() && null === $this->requestedPriorDelivery) {
            $context
                ->buildViolation('This value cannot be null.')
                ->atPath('requestedPriorDelivery')
                ->addViolation();
        }

        if ($this->getType()->isRequestableAtPurchaseOrder() && null === $this->requestedAtPurchaseOrder) {
            $context
                ->buildViolation('This value cannot be null.')
                ->atPath('requestedAtPurchaseOrder')
                ->addViolation()
            ;
        }

        if (!$this->getType()->isRequestablePriorDelivery() && null !== $this->requestedPriorDelivery) {
            $context
                ->buildViolation('You cannot request this prior delivery.')
                ->atPath('requestedPriorDelivery')
                ->addViolation()
            ;
        }

        if (!$this->getType()->isRequestableAtPurchaseOrder() && null !== $this->requestedAtPurchaseOrder) {
            $context
                ->buildViolation('You cannot request this at purchase order.')
                ->atPath('requestedAtPurchaseOrder')
                ->addViolation()
            ;
        }
    }
}
