<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Directory\Location;
use App\Repository\VaultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VaultRepository::class)]
class Vault
{
    #[ORM\Column(type: 'string', length: 255)]
    public string $path;

    #[ORM\Column(type: 'integer')]
    public int $folder;

    #[ORM\Column(type: 'integer')]
    public int $subFolder;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    public Location $location;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
