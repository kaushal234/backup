<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

interface LegacyIdInterface
{
    public function getLegacyId(): ?int;
}
