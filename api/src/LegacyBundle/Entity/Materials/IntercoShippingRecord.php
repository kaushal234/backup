<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Materials;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'isr')]
class IntercoShippingRecord
{
    #[ORM\Column(name: 'dt', type: 'datetime')]
    public \DateTimeInterface $openedAt;

    #[ORM\Column(type: 'string')]
    public string $status;

    #[ORM\Column(name: 'cuno', type: 'string')]
    public string $erpCustomerNumber;

    #[ORM\Column(name: 'ttype', type: 'string')]
    public string $transportationType;

    #[ORM\Column(name: 'cnum', type: 'string')]
    public string $containerNumber;

    #[ORM\Column(name: 'container_type', type: 'string', nullable: true)]
    public ?string $containerType = null;

    #[ORM\Column(name: 'tnum', type: 'string')]
    public string $trackingNumber;

    #[ORM\Column(name: 'dt_outb', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $outboundDate = null;

    #[ORM\Column(name: 'dt_ship', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $shippingDate = null;

    #[ORM\Column(name: 'dt_eta', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $etaDate = null;

    #[ORM\Column(type: 'text')]
    public string $notes;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'poster_id', referencedColumnName: 'id')]
    public ?PeopleById $poster = null;

    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'bu_from_id', referencedColumnName: 'id')]
    public ?LocationById $fromBusinessUnit = null;

    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'bu_to_id', referencedColumnName: 'id')]
    public ?LocationById $toBusinessUnit = null;

    /** @var Collection<IntercoShippingRecordLine> */
    #[ORM\OneToMany(targetEntity: IntercoShippingRecordLine::class, mappedBy: 'intercoShippingRecord')]
    public Collection $lines;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function __construct()
    {
        $this->lines = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }
}
