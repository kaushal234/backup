<?php

declare(strict_types=1);

namespace App\Entity\Service\TechnicianOnCall;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\Location;
use App\Entity\Service\TechnicianOnCall;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'technician_on_call_backlog_report')]
#[ORM\UniqueConstraint(name: 'unique_date_location', columns: self::UNIQ_FIELDS)]
#[UniqueEntity(
    fields: self::UNIQ_FIELDS,
    message: 'An entry already exist for this data'
)]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    routePrefix: '/service/technician_on_call',
)]
class BacklogReport
{
    private const UNIQ_FIELDS = ['sales_organisation_id', 'date'];

    #[ORM\ManyToMany(targetEntity: TechnicianOnCall::class)]
    #[ORM\JoinTable(
        name: 'technician_on_call_backlog_association',
        joinColumns: [new ORM\JoinColumn(name: 'backlog_id', referencedColumnName: 'id')],
        inverseJoinColumns: [new ORM\JoinColumn(name: 'technician_on_call_id', referencedColumnName: 'id')]
    )]
    public Collection $technicianOnCalls;

    #[Assert\NotBlank]
    #[ValidLocation(sso: true)]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'sales_organisation_id', nullable: false)]
    public Location $salesOrganisation;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'date', type: Types::DATE_IMMUTABLE)]
    public \DateTimeImmutable $date;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function __construct()
    {
        $this->technicianOnCalls = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTechnicianOnCalls(): Collection
    {
        return $this->technicianOnCalls;
    }

    public function addTechnicianOnCall(TechnicianOnCall $technicianOnCall): self
    {
        if (!$this->technicianOnCalls->contains($technicianOnCall)) {
            $this->technicianOnCalls->add($technicianOnCall);
        }

        return $this;
    }

    public function removeTechnicianOnCall(TechnicianOnCall $technicianOnCall): self
    {
        $this->technicianOnCalls->removeElement($technicianOnCall);

        return $this;
    }
}
