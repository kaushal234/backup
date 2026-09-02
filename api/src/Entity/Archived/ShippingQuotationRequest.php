<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(operations: [])]
#[ORM\Table(name: 'shipping_quotation_requests')]
class ShippingQuotationRequest
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    private People $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    private ?Location $sso = null;

    #[ORM\Column(type: 'string')]
    private string $incoterms;

    #[ORM\Column(type: 'string')]
    private string $incoLocation;

    #[ORM\Column(type: 'string')]
    private string $type;

    #[ORM\Column(type: 'string')]
    private string $answerDeadline;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: 'integer')]
    private int $counter = 0;

    #[ORM\Column(type: 'string')]
    private string $status = 'PENDING';

    /**
     * @var Collection<ShippingQuotationRequestLine>
     */
    #[ORM\OneToMany(mappedBy: 'shippingQuotationRequest', targetEntity: 'App\Entity\Archived\ShippingQuotationRequestLine', cascade: ['persist'], orphanRemoval: true)]
    private Collection $shippingQuotationRequestLines;

    /**
     * @var Collection<ShippingQuotationRequestFile>
     */
    #[ORM\OneToMany(mappedBy: 'shippingQuotationRequest', targetEntity: 'App\Entity\Archived\ShippingQuotationRequestFile', cascade: ['persist'], orphanRemoval: true)]
    private Collection $files;

    public function __construct()
    {
        $this->shippingQuotationRequestLines = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSso(): Location
    {
        return $this->sso;
    }

    public function setSso(Location $sso): self
    {
        $this->sso = $sso;

        return $this;
    }

    public function getIncoterms(): string
    {
        return $this->incoterms;
    }

    public function setIncoterms(string $incoterms): self
    {
        $this->incoterms = $incoterms;

        return $this;
    }

    public function getIncoLocation(): string
    {
        return $this->incoLocation;
    }

    public function setIncoLocation(string $incoLocation): self
    {
        $this->incoLocation = $incoLocation;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAnswerDeadline(): string
    {
        return $this->answerDeadline;
    }

    public function setAnswerDeadline(string $answerDeadline): self
    {
        $this->answerDeadline = $answerDeadline;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<ShippingQuotationRequestLine>
     */
    public function getShippingQuotationRequestLines(): Collection
    {
        return $this->shippingQuotationRequestLines;
    }

    public function addShippingQuotationRequestLine(ShippingQuotationRequestLine $shippingRequestQuotationLine): self
    {
        $this->shippingQuotationRequestLines->add($shippingRequestQuotationLine);

        $shippingRequestQuotationLine->setShippingQuotationRequest($this);

        return $this;
    }

    public function removeShippingQuotationRequestLine(ShippingQuotationRequestLine $shippingRequestQuotationLine): self
    {
        $this->shippingQuotationRequestLines->removeElement($shippingRequestQuotationLine);

        return $this;
    }

    public function getCreatedBy(): People
    {
        return $this->createdBy;
    }

    public function setCreatedBy(People $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): self
    {
        $this->counter = $counter;

        return $this;
    }

    public function incrementCounter()
    {
        ++$this->counter;
    }

    /**
     * @return Collection<ShippingQuotationRequestFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(ShippingQuotationRequestFile $file): self
    {
        $file->setShippingQuotationRequest($this);
        $this->files->add($file);

        return $this;
    }

    public function removeFile(ShippingQuotationRequestFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }
}
