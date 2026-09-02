<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering;

use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'pip')]
class ProductInnovationProposal
{
    #[ORM\Column(name: 'short_desc', type: 'string')]
    public string $shortDescription;

    #[ORM\Column(type: 'text')]
    public string $description;

    #[ORM\Column(type: 'string')]
    public string $process;

    #[ORM\Column(name: 'product_type', type: 'string')]
    public string $productType;

    #[ORM\Column(type: 'string')]
    public string $model;

    #[ORM\Column(type: 'string')]
    public string $status;

    #[ORM\Column(name: 'date', type: 'date')]
    public \DateTimeInterface $submittedAt;

    #[ORM\Column(name: 'date_closed', type: 'date')]
    public ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(name: 'date_suspended', type: 'date')]
    public ?\DateTimeInterface $suspendedAt = null;

    #[ORM\Column(name: 'days_suspended', type: 'integer')]
    public int $suspendedDays = 0;

    #[ORM\Column(type: 'text')]
    public string $resolution = '';

    #[ORM\Column(name: 'rejection_reason', type: 'text')]
    public string $rejectionReason = '';

    #[ORM\Column(name: 'ifactor', type: 'integer')]
    public int $importanceFactor;

    #[ORM\Column(name: 'final_fweight', type: 'integer')]
    public int $finalWeight = 0;

    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'factory', referencedColumnName: 'id')]
    public ?LocationById $factory = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'poster', referencedColumnName: 'id')]
    public ?PeopleById $poster = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'initiator', referencedColumnName: 'id')]
    public ?PeopleById $initiator = null;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
