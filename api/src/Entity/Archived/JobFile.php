<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use App\Entity\HumanResources\Job;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(operations: [])]
#[ORM\Table(name: 'job_files')]
#[App\Loggable(owner: 'job', ownerRelation: 'jobFiles')]
class JobFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\HumanResources\Job', inversedBy: 'jobFiles')]
    private ?Job $job = null;

    public function getJob(): Job
    {
        return $this->job;
    }

    public function setJob(Job $job): self
    {
        $this->job = $job;

        return $this;
    }
}
