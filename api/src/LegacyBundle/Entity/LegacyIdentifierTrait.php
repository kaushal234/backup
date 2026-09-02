<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use App\Security\JWT\PayloadGenerator\PayloadGeneratorInterface;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Component\Serializer\Attribute\Groups;

trait LegacyIdentifierTrait
{
    #[ORM\Column(name: 'legacy_id', type: 'integer', nullable: false)]
    #[Groups(['expose_legacy', PayloadGeneratorInterface::PAYLOAD_NORMALIZATION_GROUP])]
    #[Legacy\Id]
    protected ?int $legacyId = null;

    public function getLegacyId(): ?int
    {
        return $this->legacyId;
    }

    public function setLegacyId(int $legacyId): self
    {
        $this->legacyId = $legacyId;

        return $this;
    }
}
