<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Directory;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractLocation
{
    #[ORM\Column(type: 'integer')]
    public int $erp;

    abstract public function getName(): string;
}
