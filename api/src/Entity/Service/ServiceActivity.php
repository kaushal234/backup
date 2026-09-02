<?php

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'service_activity')]
#[ApiResource(
    operations: [
        new GetCollection(openapi: true),
        new Get(),
    ],
    routePrefix: 'service',
    normalizationContext: ['groups' => ['service_activity']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')"
)]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
])]
class ServiceActivity
{
    public const string INFO = 'Info request';
    public const string COMMISSIONING = 'Commissioning';
    public const string TROUBLESHOOTING = 'Troubleshooting';

    #[ORM\Column(name: 'name', length: 50, unique: true)]
    #[Groups(['service_activity'])]
    public string $name;

    #[ORM\Column(name: 'description', length: 50)]
    #[Groups(['service_activity'])]
    public string $description;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['service_activity'])]
    private ?int $id = null;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
