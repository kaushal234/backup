<?php

declare(strict_types=1);

namespace App\Entity\Task;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_template_name', columns: ['name'])]

class Template
{
    #[ORM\Column(type: 'string')]
    public string $name;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function getId()
    {
        return $this->id;
    }
}
