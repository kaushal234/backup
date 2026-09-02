<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Directory\NetworkRepository')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['network']]),
        new Get(),
    ],
    normalizationContext: ['groups' => ['network']],
    denormalizationContext: ['groups' => ['network']],
)]
#[ORM\Table('directory_networks')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
class Network
{
    /**
     * @var string
     */
    final public const NETWORK_TLD = 'TLD';
    /**
     * @var string
     */
    final public const NETWORK_SAS = 'SAS';
    /**
     * @var string
     */
    final public const NETWORK_AERO = 'AERO';

    #[Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', unique: true, nullable: false)]
    #[Groups(['network', 'location', 'location_detail'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
