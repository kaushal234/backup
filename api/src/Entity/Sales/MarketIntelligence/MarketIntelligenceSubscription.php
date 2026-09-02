<?php

declare(strict_types=1);

namespace App\Entity\Sales\MarketIntelligence;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\People;
use App\Entity\Sales\Competitor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ProductType;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: 'App\Repository\Sales\MarketIntelligenceSubscriptionRepository')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(security: 'user === object.getSubscriber()'),
        new Delete(security: 'user === object.getSubscriber()'),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['market_intelligence_subscription', 'market_intelligence_type', 'people_public', 'customer_list', 'competitor_list', 'catalogue_type_list']],
    denormalizationContext: ['groups' => ['market_intelligence_subscription:write']]
)]
#[ORM\Table]
#[App\Loggable]
class MarketIntelligenceSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['market_intelligence_subscription'])]
    private int $id;

    #[ORM\Column(name: 'legacy_id', type: 'integer', nullable: true)]
    private ?int $legacyId = null;

    #[ORM\ManyToOne(targetEntity: MarketIntelligenceType::class)]
    #[Groups(['market_intelligence_subscription', 'market_intelligence_subscription:write'])]
    private ?MarketIntelligenceType $type = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Competitor')]
    #[Groups(['market_intelligence_subscription', 'market_intelligence_subscription:write'])]
    private ?Competitor $competitor = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[Groups(['market_intelligence_subscription', 'market_intelligence_subscription:write'])]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ProductType')]
    #[Groups(['market_intelligence_subscription', 'market_intelligence_subscription:write'])]
    private ?ProductType $productType = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['market_intelligence_subscription', 'market_intelligence_subscription:write'])]
    private ?string $supplier = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['market_intelligence_subscription'])]
    #[Gedmo\Blameable(on: 'create')]
    private People $subscriber;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCompetitor(): ?Competitor
    {
        return $this->competitor;
    }

    public function setCompetitor(?Competitor $competitor): self
    {
        $this->competitor = $competitor;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getProductType(): ?ProductType
    {
        return $this->productType;
    }

    public function setProductType(?ProductType $productType): self
    {
        $this->productType = $productType;

        return $this;
    }

    public function getSubscriber(): People
    {
        return $this->subscriber;
    }

    public function setSubscriber(People $subscriber): self
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    public function getLegacyId(): ?int
    {
        return $this->legacyId;
    }

    /**
     * @return $this
     */
    public function setLegacyId(?int $legacyId)
    {
        $this->legacyId = $legacyId;

        return $this;
    }

    public function getType(): ?MarketIntelligenceType
    {
        return $this->type;
    }

    public function setType(?MarketIntelligenceType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    public function setSupplier(?string $supplier): self
    {
        $this->supplier = $supplier;

        return $this;
    }
}
