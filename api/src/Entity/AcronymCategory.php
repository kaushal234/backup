<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes as App;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An acronym category.
 */
#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['acronym_category']]),
        new Get(normalizationContext: ['groups' => ['acronym_category_detail']]),
    ],
)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[App\Loggable]
class AcronymCategory implements \Stringable
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['acronym_category', 'acronym_category_detail'])]
    private int $id;

    /**
     * @var string Name of the acronym category
     */
    #[ORM\Column(length: 60, unique: true, nullable: false)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Assert\Length(max: 60)]
    #[Groups(['acronym_category', 'acronym_category_detail', 'acronym'])]
    #[ApiProperty(iris: ['https://schema.org/text'])]
    private string $name;

    /**
     * @var Collection<Acronym> A list of acronyms
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Acronym', mappedBy: 'categories')]
    #[Groups(['acronym_category', 'acronym_category_detail'])]
    private Collection $acronyms;

    public function __construct()
    {
        $this->acronyms = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get category name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set category name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the acronym list for the current category.
     *
     * @return Collection<Acronym>
     */
    public function getAcronyms()
    {
        return $this->acronyms;
    }

    /**
     * Updates the acronym list.
     *
     * @param Collection<Acronym> $acronyms
     */
    public function setAcronyms(Collection $acronyms): self
    {
        $this->acronyms->clear();

        foreach ($acronyms as $acronym) {
            $this->addAcronym($acronym);
        }

        return $this;
    }

    public function addAcronym(Acronym $acronym)
    {
        if (!$this->acronyms->contains($acronym)) {
            $this->acronyms->add($acronym);
            $acronym->addCategory($this);
        }

        return $this;
    }

    public function removeAcronym(Acronym $acronym)
    {
        if ($this->acronyms->contains($acronym)) {
            $this->acronyms->removeElement($acronym);
            $acronym->removeCategory($this);
        }
    }
}
