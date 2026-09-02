<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string', length: 20)]
#[ORM\DiscriminatorMap(['catalog' => 'App\Entity\Sales\ProductFamily', 'finance' => 'App\Entity\Finance\FinanceFamily', 'manufacturing' => 'App\Entity\Manufacturing\ManufacturingFamily'])]
#[ORM\Table(name: 'families')]
abstract class Family
{
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected string $name;
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['family', 'catalogue_type_detail'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
