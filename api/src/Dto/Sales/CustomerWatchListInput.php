<?php

declare(strict_types=1);

namespace App\Dto\Sales;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CustomerWatchListInput
{
    #[Assert\Type('boolean')]
    #[Assert\NotNull]
    private bool $watchList = false;

    private ?string $watchListReason = null;

    public function isWatchList(): bool
    {
        return $this->watchList;
    }

    public function setWatchList(bool $watchList): void
    {
        $this->watchList = $watchList;
    }

    public function getWatchListReason(): ?string
    {
        return $this->watchListReason;
    }

    public function setWatchListReason(?string $watchListReason): void
    {
        $this->watchListReason = $watchListReason;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->watchList && \in_array($this->watchListReason, ['', null], true)) {
            $context
                ->buildViolation('You must give a reason when you put a customer on the watchlist')
                ->atPath('watchListReason')
                ->addViolation()
            ;
        }
    }
}
