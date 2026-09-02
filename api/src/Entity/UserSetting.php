<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Entity\Directory\People;
use App\Repository\UserSettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserSettingRepository::class)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'user_name', columns: ['user_id', 'name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(securityPostDenormalize: 'user === object.user'),
        new Get(security: 'user === object.user'),
        new Delete(security: 'user === object.user'),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['user', 'name'])]
class UserSetting
{
    #[ORM\Column(type: 'json', options: ['default' => '[]'])]
    #[Assert\Type(type: 'array')]
    #[Assert\NotBlank]
    public array $settings = [];

    #[ORM\ManyToOne(targetEntity: People::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    public People $user;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
