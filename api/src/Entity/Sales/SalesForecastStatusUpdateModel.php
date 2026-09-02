<?php

declare(strict_types=1);

namespace App\Entity\Sales;

class SalesForecastStatusUpdateModel
{
    private ?string $status = null;
    private string $comment = '';
    private bool $synchronized = false;
    private bool $cancellationPropagated = false;

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized;
    }

    public function setSynchronized(bool $synchronized): self
    {
        $this->synchronized = $synchronized;

        return $this;
    }

    public function isCancellationPropagated(): bool
    {
        return $this->cancellationPropagated;
    }

    public function setCancellationPropagated(bool $cancellationPropagated): self
    {
        $this->cancellationPropagated = $cancellationPropagated;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
