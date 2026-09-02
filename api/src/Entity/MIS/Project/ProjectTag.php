<?php

declare(strict_types=1);

namespace App\Entity\MIS\Project;

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
        new GetCollection(uriTemplate: '/project_tags'),
        new Get(uriTemplate: '/project_tags/{id}'),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['tag', 'people_public', 'expose_legacy']],
)]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[ORM\Table(name: 'project_tags')]
class ProjectTag extends AbstractTag
{
    /**
     * @var Collection<Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'project_tags_xref')]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    /**
     * @return Collection<Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        $this->projects->add($project);

        return $this;
    }

    public function removeProject(Project $project): self
    {
        $this->projects->removeElement($project);

        return $this;
    }
}
