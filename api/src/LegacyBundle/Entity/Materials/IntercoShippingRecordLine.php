<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Materials;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'isr_lines')]
class IntercoShippingRecordLine
{
    #[ORM\ManyToOne(targetEntity: IntercoShippingRecord::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[Ignore]
    public IntercoShippingRecord $intercoShippingRecord;

    #[ORM\Column(name: 't_dino', type: 'string')]
    public string $packingSlipNumber;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
