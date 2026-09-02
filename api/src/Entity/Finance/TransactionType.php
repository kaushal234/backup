<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['transaction_type']],
)]
#[ORM\Table(name: 'transaction_types')]
#[ORM\UniqueConstraint(name: 'unique_name', columns: ['name'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
class TransactionType
{
    /** @var string */
    final public const UNITS = 'Units';

    /** @var string */
    final public const SERVICE = 'Service';

    /** @var string */
    final public const MISC = 'Misc';

    /** @var string */
    final public const SPARE_PARTS = 'Spare Parts';

    /** @var string */
    final public const WARRANTY = 'Warranty';

    #[ORM\Column(type: 'string')]
    #[Groups(['transaction_type'])]
    public string $name;

    /**
     * @var Collection<TransactionTypeReference>
     */
    #[ORM\OneToMany(mappedBy: 'transactionType', targetEntity: 'App\Entity\Finance\TransactionTypeReference', cascade: ['persist'], orphanRemoval: true)]
    private Collection $transactionTypeReferences;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function __construct()
    {
        $this->transactionTypeReferences = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<TransactionTypeReference>
     */
    public function getTransactionTypeReferences(): Collection
    {
        return $this->transactionTypeReferences;
    }

    public function addTransactionTypeReference(TransactionTypeReference $transactionTypeReference): self
    {
        if (!$this->transactionTypeReferences->contains($transactionTypeReference)) {
            $transactionTypeReference->transactionType = $this;
            $this->transactionTypeReferences->add($transactionTypeReference);
        }

        return $this;
    }

    public function removeTransactionTypeReference(TransactionTypeReference $transactionTypeReference): self
    {
        if ($this->transactionTypeReferences->contains($transactionTypeReference)) {
            $this->transactionTypeReferences->removeElement($transactionTypeReference);
        }

        return $this;
    }
}
