<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'sor_units')]
class SalesOrderUnit
{
    #[ORM\ManyToOne(targetEntity: SalesOrderLine::class)]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    public SalesOrderLine $salesOrderLine;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
