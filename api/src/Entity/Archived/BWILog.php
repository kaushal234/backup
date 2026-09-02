<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table('bwi_logs')]
class BWILog
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $erp;

    #[ORM\Column(type: 'string', length: 15, nullable: false)]
    private string $login;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $operation;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $operationNumber;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $message;

    public function getId(): int
    {
        return $this->id;
    }

    public function getErp(): int
    {
        return $this->erp;
    }

    public function setErp(int $erp): self
    {
        $this->erp = $erp;

        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function getOperationNumber(): int
    {
        return $this->operationNumber;
    }

    public function setOperationNumber(int $operationNumber): self
    {
        $this->operationNumber = $operationNumber;

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

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
