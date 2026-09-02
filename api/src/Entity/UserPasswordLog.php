<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: 'App\Repository\UserPasswordLogRepository')]
#[ORM\Table]
class UserPasswordLog
{
    #[ORM\Column(name: 'password', length: 255)]
    protected string $encodedPassword;

    /**
     * Does not exist in legacy.
     */
    #[ORM\Column(length: 64)]
    protected string $salt;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getEncodedPassword(): string
    {
        return $this->encodedPassword;
    }

    public function setEncodedPassword(string $encodedPassword): self
    {
        $this->encodedPassword = $encodedPassword;

        return $this;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
