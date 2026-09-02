<?php

declare(strict_types=1);

namespace App\Entity\MIS\Project;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'mis_project_files')]
#[App\Loggable(owner: 'project', ownerRelation: 'files')]
class ProjectFile extends File
{
    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'files')]
    private ?Project $project = null;

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }
}
