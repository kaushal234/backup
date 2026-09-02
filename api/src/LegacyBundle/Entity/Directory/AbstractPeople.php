<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Directory;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractPeople
{
    #[ORM\Column(type: 'string')]
    public string $lastname;

    #[ORM\Column(type: 'string')]
    public string $firstname;

    #[ORM\Column(type: 'string')]
    public string $email;

    abstract public function getUsername(): string;
}
