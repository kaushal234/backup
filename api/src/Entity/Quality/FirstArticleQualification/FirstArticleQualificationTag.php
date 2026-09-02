<?php

declare(strict_types=1);

namespace App\Entity\Quality\FirstArticleQualification;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\AbstractTag;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/quality/first_article_qualification_tags'),
        new Get(uriTemplate: '/quality/first_article_qualification_tags/{id}'),
    ],
    normalizationContext: ['groups' => ['tag', 'people_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['tag_write']])]
#[ORM\Table(name: 'first_article_qualifications_tags')]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
class FirstArticleQualificationTag extends AbstractTag
{
    /**
     * @var Collection<FirstArticleQualification>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Quality\FirstArticleQualification\FirstArticleQualification', inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'first_article_qualifications_tags_xref')]
    private Collection $firstArticleQualifications;

    public function __construct()
    {
        $this->firstArticleQualifications = new ArrayCollection();
    }

    /**
     * @return Collection<FirstArticleQualification>
     */
    public function getFirstArticleQualifications(): Collection
    {
        return $this->firstArticleQualifications;
    }

    /**
     * @return $this
     */
    public function addFirstArticleQualification(FirstArticleQualification $firstArticleQualification): self
    {
        $this->firstArticleQualifications->add($firstArticleQualification);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeFirstArticleQualification(FirstArticleQualification $firstArticleQualification): self
    {
        $this->firstArticleQualifications->removeElement($firstArticleQualification);

        return $this;
    }
}
