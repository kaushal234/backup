<?php

declare(strict_types=1);

namespace App\Dto\Finance;

use App\Entity\Finance\ManufacturingMargin;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ManufacturingMarginBatch
{
    #[Assert\Valid]
    #[Groups(['manufacturing_margin:write'])]
    private readonly ArrayCollection $margins;

    public function __construct()
    {
        $this->margins = new ArrayCollection();
    }

    /**
     * @return Collection<ManufacturingMargin>
     */
    public function getMargins(): Collection
    {
        return $this->margins;
    }

    public function addMargin(ManufacturingMargin $margin): self
    {
        if (!$this->margins->contains($margin)) {
            $this->margins->add($margin);
        }

        return $this;
    }

    public function removeMargin(ManufacturingMargin $margin): self
    {
        $this->margins->removeElement($margin);

        return $this;
    }
}
