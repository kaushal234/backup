<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\AddressWithCountry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity('email')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['printer', 'expose_legacy']]),
        new Post(security: "is_granted('FEATURE_PRINTER_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_PRINTER_WRITE')"),
        new Delete(security: "is_granted('FEATURE_PRINTER_WRITE')"),
    ],
    routePrefix: 'support',
    normalizationContext: ['groups' => ['printer:detail', 'address', 'expose_legacy']],
    denormalizationContext: ['groups' => ['printer:write', 'address_write']],
)]
#[ORM\Table(name: 'manual_printers')]
#[ApiFilter(OrderFilter::class, properties: ['companyName' => 'ASC'])]
class ManualPrinter
{
    #[ORM\Column(type: 'string')]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['printer', 'printer:detail', 'printer:write'])]
    public string $companyName;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Groups(['printer', 'printer:detail', 'printer:write'])]
    public ?string $firstname = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Groups(['printer', 'printer:detail', 'printer:write'])]
    public ?string $lastname = null;

    #[ApiProperty(iris: ['https://schema.org/email'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Assert\Email]
    #[Groups(['printer', 'printer:detail', 'printer:write'])]
    public string $email;

    #[ORM\Embedded(class: 'App\Entity\AddressWithCountry')]
    #[Assert\Valid]
    #[Assert\NotNull]
    #[Groups(['printer:detail', 'printer:write'])]
    public AddressWithCountry $address;

    /**
     * @var Collection<ManualPrint>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Support\ManualPrint', mappedBy: 'manualPrinter', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['printer', 'printer:detail', 'printer:write'])]
    #[MaxDepth(1)]
    private Collection $prints;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['printer', 'printer:detail'])]
    private int $id;

    public function __construct()
    {
        $this->prints = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<ManualPrint>
     */
    public function getPrints(): Collection
    {
        return new ArrayCollection($this->prints->getValues());
    }

    public function addPrint(ManualPrint $print): self
    {
        if (!$this->prints->contains($print)) {
            $print->manualPrinter = $this;
            $this->prints->add($print);
        }

        return $this;
    }
}
