<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\IonTextByLanguage;
use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use App\ION\Resources\Manufacturing\JobShop\ItemInterface;
use App\ION\Resources\Manufacturing\JobShop\ItemTrait;
use App\ION\Resources\Manufacturing\JobShop\PMOCTrait;
use App\ION\Resources\TextByLanguagesTraits;
use App\ION\Serializer\Normalizer\Manufacturing\JobShop\NonConformityByItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

class IntranetViewItem implements ItemInterface, NonConformityByItemInterface
{
    use EngineeringRevisionTrait;
    use ItemTrait;
    use PMOCTrait;
    use TextByLanguagesTraits;

    #[Groups(['bom'])]
    public string $itemSelectionCode;

    #[Groups(['bom'])]
    public int $level;

    #[Groups(['bom'])]
    public int $position;

    #[Groups(['bom'])]
    public string $engineeringDescription;

    #[Groups(['bom'])]
    public string $partNumberProject;

    #[Groups(['bom'])]
    public string $engineeringSignalCode;

    #[Groups(['bom'])]
    public string $extraInformation;

    #[Groups(['bom'])]
    public int $operation;

    #[Groups(['bom'])]
    public string $engineeringRevisionDescription;

    #[Groups(['bom'])]
    public ?string $engineeringRevisionDrawing = null;

    #[Groups(['bom'])]
    public string $itemSignalCode;

    /** @var Collection<IonTextByLanguage> */
    #[Groups(['bom'])]
    public Collection $conflictItemTexts;

    /**
     * @var Collection<ItemInterface>
     */
    #[Groups(['bom'])]
    protected Collection $items;

    public function __construct()
    {
        /* @var Collection<IonTextByLanguage> textByLanguages */
        $this->textByLanguages = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->conflictItemTexts = new ArrayCollection();
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param ArrayCollection|ItemInterface[] $items
     */
    public function setItems(Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(ItemInterface $item): self
    {
        $this->items->add($item);

        return $this;
    }

    public function removeItem(ItemInterface $item): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }

    public function getPartNumber(): string
    {
        return $this->partNumber;
    }

    public function setConflictItemTexts(Collection $conflictItemTexts): self
    {
        $this->conflictItemTexts = $conflictItemTexts;

        return $this;
    }

    public function addConflictItemText(IonTextByLanguage $textBylanguage): self
    {
        $this->items->add($textBylanguage);

        return $this;
    }

    public function removeConflictItemText(IonTextByLanguage $textByLanguage): self
    {
        // do nothing, we do not remove element from this resource
        return $this;
    }
}
