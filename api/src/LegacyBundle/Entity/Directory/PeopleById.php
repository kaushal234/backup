<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Directory;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'people')]
class PeopleById extends AbstractPeople
{
    #[ORM\Column(type: 'string')]
    public string $username;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    public function getUsername(): string
    {
        return $this->username;
    }
}
