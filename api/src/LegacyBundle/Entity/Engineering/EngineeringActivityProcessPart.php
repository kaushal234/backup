<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Engineering;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'eap_parts')]
class EngineeringActivityProcessPart
{
    #[ORM\ManyToOne(targetEntity: EngineeringActivityProcess::class, inversedBy: 'parts')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[Ignore]
    public EngineeringActivityProcess $engineeringActivityProcess;

    #[ORM\Column(name: 'pn', type: 'string')]
    public string $partNumber;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
