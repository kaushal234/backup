<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class PlannedCompletionDates
{
    #[ORM\Column(name: 'pcd_0', type: 'date', nullable: true)]
    public ?\DateTimeInterface $milestone0 = null;

    #[ORM\Column(name: 'pcd_1', type: 'date', nullable: true)]
    public ?\DateTimeInterface $milestone1 = null;

    #[ORM\Column(name: 'pcd_2', type: 'date', nullable: true)]
    public ?\DateTimeInterface $milestone2 = null;

    #[ORM\Column(name: 'pcd_3', type: 'date', nullable: true)]
    public ?\DateTimeInterface $milestone3 = null;

    #[ORM\Column(name: 'pcd_4', type: 'date', nullable: true)]
    public ?\DateTimeInterface $milestone4 = null;
}
