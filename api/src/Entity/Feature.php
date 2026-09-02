<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\FeatureController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Division;
use App\Entity\Directory\SubDivision;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\FeatureRepository')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/grants',
            controller: FeatureController::class,
            name: 'grants',
        ),
        new Get(),
        new Put(security: "is_granted('FEATURE_GROUPS_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['feature', 'expose_legacy']],
    denormalizationContext: ['groups' => ['feature_write']],
)]
#[ORM\Table]
#[ORM\Index(columns: ['name'], name: 'feature_name_idx')]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'ASC', 'name' => 'ASC'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['groups' => 'exact', 'groups.acls.user' => 'exact', 'groups.acls.location' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['feature_list']])]
#[App\Loggable]
class Feature
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['feature'])]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string', length: 100, unique: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank]
    #[Groups(['feature', 'group_detail', 'feature_list', 'feature_write'])]
    private string $name;

    /**
     * @var Collection<Group>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Group', inversedBy: 'features', cascade: ['persist'])]
    #[Groups(['feature', 'feature_write'])]
    private Collection $groups;

    /**
     * @var Collection<AuthorizedApplication>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\AuthorizedApplication', inversedBy: 'features', cascade: ['persist'])]
    private Collection $authorizedApplications;

    /**
     * @var Collection<Division>
     */
    #[ORM\ManyToMany(targetEntity: SubDivision::class, inversedBy: 'features', cascade: ['persist'])]
    private Collection $subDivisions;

    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->authorizedApplications = new ArrayCollection();
        $this->subDivisions = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addFeature($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }

    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function addAuthorizedApplication(AuthorizedApplication $authorizedApplication)
    {
        if (!$this->authorizedApplications->contains($authorizedApplication)) {
            $this->authorizedApplications->add($authorizedApplication);
            $authorizedApplication->addFeature($this);
        }

        return $this;
    }

    public function removeAuthorizedApplication(AuthorizedApplication $authorizedApplication)
    {
        if ($this->authorizedApplications->contains($authorizedApplication)) {
            $this->authorizedApplications->removeElement($authorizedApplication);
        }

        return $this;
    }

    public function getAuthorizedApplications(): Collection
    {
        return $this->authorizedApplications;
    }

    public function addSubDivision(SubDivision $subDivision)
    {
        if (!$this->subDivisions->contains($subDivision)) {
            $this->subDivisions->add($subDivision);
            $subDivision->addFeature($this);
        }

        return $this;
    }

    public function removeSubDivision(SubDivision $subDivision)
    {
        if ($this->subDivisions->contains($subDivision)) {
            $this->subDivisions->removeElement($subDivision);
        }

        return $this;
    }

    public function getSubDivisions(): Collection
    {
        return $this->subDivisions;
    }
}
