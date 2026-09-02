<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['erp', 'customerPurchaseOrderNumber', 'position'], groups: ['zpl:write'])]
#[ORM\Table(name: 'zpl_printing_request_details')]
class ZplPrintingRequestDetail
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(groups: ['Default', 'zpl:write'])]
    #[Groups(['zpl:write', 'zpl:print'])]
    private int $erp;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotNull(groups: ['Default', 'zpl:write'])]
    #[Groups(['zpl:write', 'zpl:print'])]
    private string $customerPurchaseOrderNumber;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull(groups: ['Default', 'zpl:write'])]
    #[Groups(['zpl:write', 'zpl:print'])]
    private int $position;

    #[ORM\Column(type: 'text')]
    #[Assert\NotNull(groups: ['zpl:write'])]
    #[Groups(['zpl:write'])]
    private ?string $zpl = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $printedAt = null;

    public function getErp(): int
    {
        return $this->erp;
    }

    public function setErp(int $erp): self
    {
        $this->erp = $erp;

        return $this;
    }

    public function getCustomerPurchaseOrderNumber(): string
    {
        return $this->customerPurchaseOrderNumber;
    }

    public function setCustomerPurchaseOrderNumber(string $customerPurchaseOrderNumber): self
    {
        $this->customerPurchaseOrderNumber = $customerPurchaseOrderNumber;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getZpl(): ?string
    {
        return $this->zpl;
    }

    public function setZpl(string $zpl): self
    {
        $this->zpl = $zpl;

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

    public function getPrintedAt(): ?\DateTimeInterface
    {
        return $this->printedAt;
    }

    public function setPrintedAt(?\DateTimeInterface $printedAt): self
    {
        $this->printedAt = $printedAt;

        return $this;
    }
}
