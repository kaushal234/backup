<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\People;
use App\Entity\Directory\SubDivision;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['main' => 'App\Entity\Sales\MainSalesRepresentative', 'secondary' => 'App\Entity\Sales\SecondarySalesRepresentative'])]
#[ORM\Table(name: 'customer_sales_representatives')]
abstract class AbstractSalesRepresentative implements \Stringable
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['customer', 'customer_write', 'sales_representative_campaign'])]
    #[Transferable(manager: 'manager.customer.representative')]
    public People $asm;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\SubDivision')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['customer', 'customer_write'])]
    public SubDivision $subDivision;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function __toString()
    {
        return \sprintf('ASM: %s / SubDivision: %s', $this->asm->getDisplayName(), $this->subDivision->name);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
