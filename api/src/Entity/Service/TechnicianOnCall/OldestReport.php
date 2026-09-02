<?php

declare(strict_types=1);

namespace App\Entity\Service\TechnicianOnCall;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\Location;
use App\Entity\Service\TechnicianOnCall;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'technician_on_call_oldest_report')]
#[ORM\UniqueConstraint(name: 'unique_toc_oldest', columns: self::UNIQ_FIELDS)]
#[UniqueEntity(
    fields: self::UNIQ_FIELDS,
    message: 'An entry already exist for this data'
)]
#[ApiResource(
    shortName: 'technician_on_calls_oldest_report',
    operations: [
        new Get(),
        new GetCollection(),
    ],
    routePrefix: 'service',
)]
class OldestReport
{
    private const UNIQ_FIELDS = ['technician_on_call_id', 'sales_organisation_id', 'date'];

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: TechnicianOnCall::class)]
    #[ORM\JoinColumn(name: 'technician_on_call_id', nullable: false)]
    public TechnicianOnCall $technicianOnCall;

    #[Assert\NotBlank]
    #[ValidLocation(sso: true)]
    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'sales_organisation_id', nullable: false)]
    public Location $salesOrganisation;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'date', type: Types::DATE_IMMUTABLE)]
    public \DateTimeInterface $date;

    #[Assert\Type('integer')]
    #[Assert\GreaterThanOrEqual(0)]
    #[ORM\Column(type: Types::INTEGER)]
    public int $days;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
