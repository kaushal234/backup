<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'transportation_notes_files')]
#[App\Loggable(owner: 'note', ownerRelation: 'files')]
class TransportationNoteFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Parts\TransportationNote', inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private TransportationNote $note;

    public function getNote(): TransportationNote
    {
        return $this->note;
    }

    public function setNote(TransportationNote $note): self
    {
        $this->note = $note;

        return $this;
    }
}
