<?php

declare(strict_types=1);

namespace App\Dto\Emails;

use App\Entity\Directory\People;
use Symfony\Component\Validator\Constraints as Assert;

class ResourceEmail
{
    #[Assert\Email(mode: 'strict')]
    private ?string $from = null;

    #[Assert\All(constraints: [new Assert\Email(mode: 'strict')])]
    #[Assert\Count(min: 1, minMessage: 'You must specify at least one recipient.')]
    private array $to = [];

    #[Assert\All(constraints: [new Assert\Email(mode: 'strict')])]
    private array $cc = [];

    #[Assert\All(constraints: [new Assert\Email(mode: 'strict')])]
    private array $bcc = [];

    private array $context = [];

    private ?string $note;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?string $iri = null;

    #[Assert\Url(requireTld: true)]
    private ?string $link = null;

    private ?string $locale = null;

    private ?People $sender = null;

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function setTo(array $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function addTo(string $to): self
    {
        $this->to[] = $to;

        return $this;
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function setCc(array $cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    public function addCc(string $cc): self
    {
        $this->cc[] = $cc;

        return $this;
    }

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function setBcc(array $bcc): self
    {
        $this->bcc = $bcc;

        return $this;
    }

    public function addBcc(string $bcc): self
    {
        $this->bcc[] = $bcc;

        return $this;
    }

    public function getIri(): string
    {
        return $this->iri;
    }

    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getSender(): ?People
    {
        return $this->sender;
    }

    public function setSender(?People $sender): ?self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getContext(): array
    {
        return [
            'link' => $this->link,
            'locale' => $this->locale,
            'sender' => [
                'firstname' => $this->sender->getFirstname() ?? null,
                'lastname' => $this->sender->getLastname() ?? null,
            ],
            'note' => $this->note ?? null,
        ];
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }
}
