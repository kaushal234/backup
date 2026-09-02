<?php

declare(strict_types=1);

namespace App\Entity\Quality\FirstArticleQualification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Quality\FirstArticleQualification\PlanItemTypeRepository')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['faq_item_type_detail']]),
        new Post(security: "is_granted('FEATURE_FAQ_ITEM_TYPE_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_FAQ_ITEM_TYPE_WRITE')"),
        new Delete(security: "is_granted('FEATURE_FAQ_ITEM_TYPE_DELETE')"),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['faq_item_type_detail']],
    denormalizationContext: ['groups' => ['faq_item_type_write']],
)]
#[ORM\Table(name: 'first_article_qualifications_plan_item_types')]
#[App\Loggable]
class PlanItemType
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['faq_item_type_detail', 'faq_item_type_write'])]
    private string $description;

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    #[Groups(['faq_item_type_detail', 'faq_item_type_write'])]
    private bool $requestablePriorDelivery;

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Assert\Type(type: 'bool')]
    #[Assert\NotNull]
    #[Groups(['faq_item_type_detail', 'faq_item_type_write'])]
    private bool $requestableAtPurchaseOrder;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isRequestablePriorDelivery(): bool
    {
        return $this->requestablePriorDelivery;
    }

    public function setRequestablePriorDelivery(bool $requestablePriorDelivery): self
    {
        $this->requestablePriorDelivery = $requestablePriorDelivery;

        return $this;
    }

    public function isRequestableAtPurchaseOrder(): bool
    {
        return $this->requestableAtPurchaseOrder;
    }

    public function setRequestableAtPurchaseOrder(bool $requestableAtPurchaseOrder): self
    {
        $this->requestableAtPurchaseOrder = $requestableAtPurchaseOrder;

        return $this;
    }
}
