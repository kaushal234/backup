<?php

declare(strict_types=1);

namespace App\Entity\MinutesOfMeeting;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('MEETING_READ_VOTER', object.getMeeting())"),
    ],
    routePrefix: 'minutes_of_meeting',
    normalizationContext: ['meeting:detail']
)]
#[ORM\Table(name: 'meeting_contacts')]
class Contact
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private string $firstName;

    #[ORM\Column(type: 'string')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private string $lastName;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['meeting:detail', 'meeting:write'])]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['meeting:detail', 'meeting:write'])]
    #[Assert\Email]
    private ?string $mail = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    #[Assert\NotBlank]
    private string $company;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['meeting:detail', 'meeting:write'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\MinutesOfMeeting\Meeting', inversedBy: 'contacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meeting $meeting = null;

    public function reset(): void
    {
        $this->id = null;
        $this->meeting = null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(Meeting $meeting): self
    {
        $this->meeting = $meeting;

        return $this;
    }
}
