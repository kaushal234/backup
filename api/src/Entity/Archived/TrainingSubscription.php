<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Directory\People;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ApiResource(operations: [])]
#[UniqueEntity(fields: ['subscriber', 'category', 'type'], message: "You've already subscribed to this combinaison.")]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_subscription_by_type', columns: ['subscriber_id', 'category_id', 'type_id'])]
class TrainingSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: TrainingCategory::class)]
    private ?TrainingCategory $category = null;

    #[ORM\ManyToOne(targetEntity: TrainingType::class)]
    private ?TrainingType $type = null;

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: false)]
    private People $subscriber;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategory(): ?TrainingCategory
    {
        return $this->category;
    }

    public function setCategory(?TrainingCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): ?TrainingType
    {
        return $this->type;
    }

    public function setType(?TrainingType $type): self
    {
        $this->type = $type;

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
}
