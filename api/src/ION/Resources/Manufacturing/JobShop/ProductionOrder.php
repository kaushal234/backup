<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use Symfony\Component\Serializer\Attribute\Groups;

class ProductionOrder
{
    #[Groups(['project'])]
    public string $productionOrderIdentifier;

    #[Groups(['project'])]
    public string $status;

    #[Groups(['project'])]
    public ?\DateTimeInterface $productionStart = null;

    /**
     * @var Operation[]
     */
    #[Groups(['project'])]
    private array $operations = [];

    public function getOperations(): array
    {
        return $this->operations;
    }

    public function addOperation(Operation $operation): self
    {
        $this->operations[] = $operation;

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
