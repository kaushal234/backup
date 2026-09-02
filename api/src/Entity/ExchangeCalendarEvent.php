<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\ExchangeCalendarEventRepository')]
#[ORM\Table(name: 'exchange_calendar_events')]
class ExchangeCalendarEvent
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'acls')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(name: 'resource', type: 'string', length: 255)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 255)]
    private string $resource;

    #[ORM\Column(name: 'exchange_id', type: 'string', length: 255)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $exchangeId;

    #[ORM\Column(name: 'exchange_change_key', type: 'string', length: 50)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private string $exchangeChangeKey;

    public function __construct(User $user, string $resource, string $exchangeId, string $exhangeChangeKey)
    {
        $this->user = $user;
        $this->resource = $resource;
        $this->exchangeId = $exchangeId;
        $this->exchangeChangeKey = $exhangeChangeKey;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getExchangeId(): string
    {
        return $this->exchangeId;
    }

    public function getExchangeChangeKey(): string
    {
        return $this->exchangeChangeKey;
    }

    public function getResource(): string
    {
        return $this->resource;
    }
}
