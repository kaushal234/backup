<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(normalizationContext: ['groups' => ['authorized_application', 'people_public', 'authorized_application:private', 'feature_list']]),
        new Get(security: "is_granted('FEATURE_AUTHORIZED_APPLICATION_ADMIN') or user === object"),
        new Put(denormalizationContext: ['groups' => ['authorized_application:edit']]),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['authorized_application', 'authorized_application:detail', 'people_public', 'feature_list']],
    denormalizationContext: ['groups' => ['authorized_application:create']],
    security: "is_granted('FEATURE_AUTHORIZED_APPLICATION_ADMIN')",
)]
#[ORM\Table]
class AuthorizedApplication implements \Stringable, UserInterface
{
    #[ORM\Column(unique: true)]
    #[Assert\NotBlank]
    #[Groups(['authorized_application', 'authorized_application:create'])]
    public string $name;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['authorized_application', 'authorized_application:create', 'authorized_application:edit'])]
    public bool $disabled = false;

    #[Groups(['authorized_application:private'])]
    public ?string $key = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    #[Groups(['authorized_application', 'authorized_application:create'])]
    public \DateTime $keyExpiresOn;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Groups(['authorized_application'])]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTime $keyGeneratedOn;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by')]
    #[Groups(['authorized_application'])]
    #[Gedmo\Blameable(on: 'create')]
    public People $createdBy;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['authorized_application'])]
    private int $id;

    /**
     * @var Collection<Feature>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Feature', mappedBy: 'authorizedApplications')]
    #[Groups(['authorized_application', 'authorized_application:create', 'authorized_application:edit'])]
    private Collection $features;

    public function __construct()
    {
        $this->features = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addFeature(Feature $feature): self
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->addAuthorizedApplication($this);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): self
    {
        if ($this->features->contains($feature)) {
            $feature->removeAuthorizedApplication($this);
            $this->features->removeElement($feature);
        }

        return $this;
    }

    /**
     * @return Collection<Feature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function getUsername(): string
    {
        return $this->name;
    }

    public function getUserIdentifier(): string
    {
        return $this->name;
    }
}
