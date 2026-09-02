<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'sor_lines')]
class SalesOrderLine
{
    #[ORM\Column(name: 'conf_wrty_erp', length: 1)]
    public string $warrantyAccepted;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
