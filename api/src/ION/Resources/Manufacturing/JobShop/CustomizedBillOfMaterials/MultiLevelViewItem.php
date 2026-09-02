<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use App\ION\Resources\Manufacturing\JobShop\ItemTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

class MultiLevelViewItem implements ItemInterface
{
    use EngineeringRevisionTrait;
    use ItemTrait;
    #[Groups(['cbom'])]
    public int $level;

    #[Groups(['cbom'])]
    public int $position;

    #[Groups(['cbom'])]
    public int $operation;

    #[Groups(['cbom'])]
    public string $engineeringSignalCode;

    #[Groups(['cbom'])]
    public bool $phantom;

    /**
     * @var Collection<MultiLevelViewItem>
     */
    #[Groups(['cbom'])]
    protected Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function addChild(self $child): self
    {
        $this->children->add($child);

        return $this;
    }

    public function removeChild(self $child): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
