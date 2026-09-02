<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [])]
#[ORM\Entity]
#[ORM\Table(name: 'trainings_files')]
class TrainingFile extends File
{
    #[ORM\ManyToOne(targetEntity: Training::class, inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private Training $training;

    public function getTraining(): Training
    {
        return $this->training;
    }

    public function setTraining(Training $training): self
    {
        $this->training = $training;

        return $this;
    }
}
