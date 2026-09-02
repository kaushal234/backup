<?php

declare(strict_types=1);

namespace App\Entity\SPQ;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
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
    routePrefix: 'parts',
    normalizationContext: ['groups' => ['spq_request_type']]
)]
#[ORM\Table(name: 'spq_request_types')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
class RequestType
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['spq_request_type'])]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 30, unique: true)]
    #[Groups(['spq_request_type', 'quotation', 'quotation:detail'])]
    private string $name;

    /**
     * @var Quotation[]|ArrayCollection
     */
    #[ORM\OneToMany(mappedBy: 'requestType', targetEntity: 'App\Entity\SPQ\Quotation')]
    private Collection $quotations;

    public function __construct()
    {
        $this->quotations = new ArrayCollection();
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
     * @return Collection<Quotation>
     */
    public function getQuotations(): Collection
    {
        return $this->quotations;
    }
}
