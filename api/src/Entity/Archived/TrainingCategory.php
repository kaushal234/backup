<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ApiResource(operations: [])]
#[UniqueEntity(fields: ['name'])]
#[ORM\Table(name: 'trainings_categories')]
class TrainingCategory
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 30, unique: true)]
    private string $name;

    /**
     * @var Collection<Training>
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Training::class)]
    private Collection $trainings;

    public function __construct()
    {
        $this->trainings = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<Training>
     */
    public function getTrainings(): Collection
    {
        return $this->trainings;
    }
}
