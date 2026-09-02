<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class LifecycleDates
{
    #[ORM\Column(name: 'date', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $createdOn = null;

    #[ORM\Column(name: 'date_closed', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $closedOn = null;

    #[ORM\Column(name: 'date_suspended', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $suspendedOn = null;

    #[ORM\Column(name: 'days_suspended', type: 'integer')]
    public int $suspendedDaysCount = 0;
}
