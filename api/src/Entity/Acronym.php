<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['acronym'])]
#[ApiResource(
    operations: [
        new GetCollection(formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']]),
        new Post(security: "is_granted('FEATURE_ACRONYM_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_ACRONYM_WRITE')"),
        new Delete(security: "is_granted('FEATURE_ACRONYM_WRITE')"),
    ],
    normalizationContext: ['groups' => ['acronym', 'expose_legacy']],
    denormalizationContext: ['groups' => ['acronym_write']],
)]
#[ORM\Table]
#[ORM\Index(columns: ['acronym', 'short_description', 'url'])]
#[ApiFilter(OrderFilter::class, properties: ['acronym' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['acronym' => 'exact', 'description' => 'partial', 'shortDescription' => 'partial', 'categories' => 'exact', 'legacyId' => 'exact'])]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['acronym_export']])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['acronym' => 'exact', 'description' => 'partial', 'shortDescription' => 'partial'])]
#[ApiFilter(ColumnsFilter::class)]
#[App\Loggable]
#[Legacy\Synchronize(table: 'agr')]
class Acronym implements \Stringable, LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['acronym'])]
    #[Legacy\Column(column: 'acronym')]
    private ?int $id = null;

    /**
     * @var string The acronym
     */
    #[ORM\Column(length: 10, unique: true, nullable: false)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 10)]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['acronym', 'acronym_write', 'acronym_export'])]
    #[Legacy\Column(column: 'acronym')]
    private string $acronym;

    /**
     * @var string Description of the acronym
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ApiProperty(iris: ['https://schema.org/description'])]
    #[Groups(['acronym', 'acronym_write', 'acronym_export'])]
    #[Legacy\Column(column: 'descb')]
    private ?string $description = null;

    /**
     * @var string Short description of the acronym
     */
    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 100)]
    #[ApiProperty(iris: ['https://schema.org/description'])]
    #[Groups(['acronym', 'acronym_write', 'acronym_export'])]
    #[Legacy\Column(column: 'desca')]
    private string $shortDescription;

    /**
     * @var string Url to which points the acronym
     */
    #[ORM\Column(nullable: true, type: 'string', length: 120)]
    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 120)]
    #[ApiProperty(iris: ['https://schema.org/url'])]
    #[Groups(['acronym', 'acronym_write'])]
    #[Legacy\Column(column: 'url')]
    private ?string $url = null;

    /**
     * @var Collection<AcronymCategory> A list to which the acronym is attached
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\AcronymCategory', inversedBy: 'acronyms', cascade: ['persist'], fetch: 'EAGER')]
    #[Groups(['acronym', 'acronym_write'])]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->acronym;
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get acronym.
     */
    public function getAcronym(): string
    {
        return $this->acronym;
    }

    /**
     * Set short description.
     *
     * @return $this
     */
    public function setAcronym(string $acronym): self
    {
        $this->acronym = $acronym;

        return $this;
    }

    /**
     * Get short description.
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * Set short description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setShortDescription($description): self
    {
        $this->shortDescription = $description;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get Url.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set Url.
     *
     * @return $this
     */
    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Fetches the categories associated to the acronym.
     *
     * @return Collection<AcronymCategory>
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Sets the list of acronym categories.
     *
     * @param Collection<AcronymCategory> $categories
     */
    #[Groups(['acronym'])]
    public function setCategories(Collection $categories): self
    {
        $this->categories->clear();

        foreach ($categories as $category) {
            $this->addCategory($category);
        }

        return $this;
    }

    /**
     * Adds a new category to the current acronym.
     *
     * @return $this
     */
    public function addCategory(AcronymCategory $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    /**
     * Removes the specified category from the acronym.
     *
     * @return $this
     */
    public function removeCategory(AcronymCategory $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
        }

        return $this;
    }
}
