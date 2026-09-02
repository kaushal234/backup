<?php

declare(strict_types=1);

namespace App\Entity\MinutesOfMeeting;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'meeting_files')]
#[App\Loggable(owner: 'meeting', ownerRelation: 'files')]
class MeetingFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\MinutesOfMeeting\Meeting', inversedBy: 'files')]
    private ?Meeting $meeting = null;

    public function getMeeting(): Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(Meeting $meeting): self
    {
        $this->meeting = $meeting;

        return $this;
    }
}
