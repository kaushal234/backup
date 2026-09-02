<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Parts;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'spr')]
class SparePartsRequest
{
    #[ORM\Column(name: 'status', type: 'string')]
    #[Groups(['legacy:warranty_claim:parts'])]
    public string $status = '';

    #[ORM\Column(name: 'dt', type: 'datetime')]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Groups(['legacy:warranty_claim:parts'])]
    public \DateTimeInterface $createdAt;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[Groups(['legacy:warranty_claim:parts'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
