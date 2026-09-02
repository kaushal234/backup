<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(
            security: "is_granted('FEATURE_MODULE_WRITE')"
        ),
        new Get(),
        new Put(
            security: "is_granted('FEATURE_MODULE_WRITE')"
        ),
    ]
)]
#[ORM\Table(name: 'third_party_app_security_level')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
class SecurityLevel
{
    #[ORM\Column(length: 20)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    #[Groups(['security_level'])]
    public string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[Groups(['security_level'])]
    public ?string $description = null;
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['security_level'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
