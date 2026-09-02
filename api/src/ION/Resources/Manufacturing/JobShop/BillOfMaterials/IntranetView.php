<?php

declare(strict_types=1);

namespace App\ION\Resources\Manufacturing\JobShop\BillOfMaterials;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Factory\VaultFileDownloadableInterface;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\DataAreaFilter;
use App\ION\Resources\IonTextByLanguage;
use App\ION\Resources\Manufacturing\JobShop\EngineeringRevisionTrait;
use App\ION\Resources\Manufacturing\JobShop\PMOCTrait;
use App\ION\Resources\TextByLanguagesTraits;
use App\ION\Serializer\Normalizer\Manufacturing\JobShop\NonConformityByItemContextNormalizer;
use App\ION\Serializer\Normalizer\Manufacturing\JobShop\NonConformityByItemInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*']),
    ],
    routePrefix: 'ion/bill-of-materials',
    normalizationContext: ['groups' => ['bom', 'ion:text', 'ion:pmoc', 'ion:engineering:revision', 'ion:item']],
    denormalizationContext: [],
    provider: CachedIONItemDataProvider::class,
)]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalizationGroups', 'overrideDefaultGroups' => false, 'whitelist' => [NonConformityByItemContextNormalizer::NORMALIZATION_GROUP]])]
#[ApiFilter(DataAreaFilter::class, properties: ['date', 'otherLanguage'])]
class IntranetView extends BillOfMaterials implements NonConformityByItemInterface, VaultFileDownloadableInterface
{
    use EngineeringRevisionTrait;
    use PMOCTrait;
    use TextByLanguagesTraits;

    #[Groups(['bom'])]
    public string $engineeringDescription;

    #[Groups(['bom'])]
    public string $itemSelectionCode;

    #[Groups(['bom'])]
    public string $engineeringSignalCode;

    #[Groups(['bom'])]
    public string $engineeringRevisionDescription;

    #[Groups(['bom'])]
    public string $itemSignalCode;

    /** @var Collection<IonTextByLanguage> */
    #[Groups(['bom'])]
    public Collection $conflictItemTexts;

    #[Groups(['bom'])]
    public ?string $engineeringRevisionDrawing = null;

    /**
     * @var Collection<IntranetViewItem>
     */
    protected Collection $items;

    public function __construct()
    {
        parent::__construct();

        /* @var Collection<IonTextByLanguage> textByLanguages */
        $this->textByLanguages = new ArrayCollection();
        $this->conflictItemTexts = new ArrayCollection();
    }

    public function getPartNumber(): string
    {
        return $this->product;
    }

    public function getRevision(): string
    {
        return $this->engineeringRevision;
    }

    public function getSignalCode(): string
    {
        return $this->engineeringSignalCode;
    }

    public function getDrawing(): ?string
    {
        return $this->engineeringRevisionDrawing;
    }

    public function getSite(): int
    {
        return $this->site;
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
