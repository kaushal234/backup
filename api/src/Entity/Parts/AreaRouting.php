<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['epartsOrderRoutingConfiguration', 'area'])]
#[ApiResource(operations: [new Get()], routePrefix: 'parts')]
#[ORM\Table('eparts_area_routings')]
class AreaRouting
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Parts\EpartsOrderRoutingConfiguration', inversedBy: 'areaRoutings')]
    #[ORM\JoinColumn(nullable: false)]
    public EpartsOrderRoutingConfiguration $epartsOrderRoutingConfiguration;

    #[ORM\Column(type: 'string', length: 3)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 3)]
    #[Groups(['eparts_configuration:detail', 'eparts_configuration:update', 'eparts_configuration:create'])]
    public string $area;

    #[ORM\Column(type: 'string', length: 3)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 3)]
    #[Groups(['eparts_configuration:detail', 'eparts_configuration:update', 'eparts_configuration:create'])]
    public string $routing;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
