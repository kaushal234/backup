<?php

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Validator\Constraints\LockedValue;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'technician_on_call_type')]
#[LockedValue(value: self::NOT_DEFINE_YET, propertyPath: 'name')]
#[LockedValue(value: self::CUSTOMER, propertyPath: 'name')]
#[LockedValue(value: self::SSO, propertyPath: 'name')]
#[LockedValue(value: self::FACTORY, propertyPath: 'name')]
#[ApiResource(
    operations: [
        new GetCollection(openapi: true),
        new Get(openapi: true),
    ],
    routePrefix: 'service',
    normalizationContext: ['groups' => ['technician_on_call_type']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')"
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
])]
class TechnicianOnCallType
{
    public const NOT_DEFINE_YET = 'toc.type.not_define_yet';
    public const CUSTOMER = 'toc.type.customer';
    public const SSO = 'toc.type.sso';
    public const FACTORY = 'toc.type.factory';

    #[ORM\Column(name: 'name', length: 50, unique: true)]
    #[Groups(['technician_on_call_type'])]
    public string $name;

    #[ORM\Column(name: 'description', length: 50)]
    #[Groups(['technician_on_call_type'])]
    public string $description;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['technician_on_call_type'])]
    private ?int $id = null;

    public function __toString(): string
    {
        return $this->description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
