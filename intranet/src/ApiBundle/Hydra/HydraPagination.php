<?php

declare(strict_types=1);

namespace ApiBundle\Hydra;

class HydraPagination
{
    private readonly int $totalItems;

    private ?string $nextPage = null;

    private int $estimatedNumberOfPages = 1;

    private ?bool $enabled = null;

    public function __construct(array $data)
    {
        $this->totalItems = \array_key_exists('hydra:totalItems', $data) ? (int) $data['hydra:totalItems'] : 0;
        if (\array_key_exists('hydra:view', $data) && \is_array($data['hydra:view'])) {
            $this->enabled = true;

            if (isset($data['hydra:view']['hydra:next'])) {
                $this->nextPage = $data['hydra:view']['hydra:next'];
            }

            $membersNumber = \count($data['hydra:member']);
            if ($this->totalItems > 0 && $membersNumber > 0) {
                $this->estimatedNumberOfPages = (int) ceil($this->totalItems / $membersNumber);
            }
        }
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function hasNextPage(): bool
    {
        return null !== $this->nextPage;
    }

    public function getEstimatedNumberOfPages(): int
    {
        return $this->estimatedNumberOfPages;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
