<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop;

use App\Factory\VaultFileDownloadableInterface;
use App\ION\Resources\IonTextByLanguage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @deprecated
 */
class CustomizedBillOfMaterialsItem implements VaultFileDownloadableInterface
{
    use PartNumberTrait;

    #[Groups(['cbom'])]
    public ?string $standardItemProject = null;

    #[Groups(['cbom'])]
    public string $standardItem;

    #[Groups(['cbom'])]
    public int $position;

    #[Groups(['cbom'])]
    public ?string $partNumberProject = null;

    #[Groups(['cbom'])]
    public string $partNumber;

    #[Groups(['cbom'])]
    public float $quantity;

    #[Groups(['cbom'])]
    public float $productQuantity;

    #[Groups(['cbom'])]
    public int $level;

    #[Groups(['cbom'])]
    public string $operation;

    #[Groups(['cbom'])]
    public string $customOperation;

    /** @var Collection<IonTextByLanguage> */
    #[Groups(['cbom'])]
    public Collection $conflictItemTexts;

    private int $site;

    /**
     * @var Collection<CustomizedBillOfMaterialsItem>
     */
    #[Groups(['cbom'])]
    private Collection $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->conflictItemTexts = new ArrayCollection();
    }

    /**
     * @return Collection<CustomizedBillOfMaterialsItem>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(?self $child): self
    {
        $this->children->add($child);

        return $this;
    }

    public function removeChild(self $child): self
    {
        $this->children->removeElement($child);

        return $this;
    }

    public function getPartNumber(): string
    {
        return $this->partNumber;
    }

    public function getRevision(): string
    {
        return $this->engineeringRevision;
    }

    public function setSite(int $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getSite(): int
    {
        return $this->site;
    }

    public function getSignalCode(): string
    {
        return $this->itemSignalCode;
    }

    public function getDrawing(): ?string
    {
        return $this->engineeringRevisionDrawing;
    }

    public function setConflictItemTexts(Collection $conflictItemTexts): self
    {
        $this->conflictItemTexts = $conflictItemTexts;

        return $this;
    }

    public function addConflictItemText(IonTextByLanguage $textBylanguage): self
    {
        $this->conflictItemTexts->add($textBylanguage);

        return $this;
    }

    public function removeConflictItemText(IonTextByLanguage $textByLanguage): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
