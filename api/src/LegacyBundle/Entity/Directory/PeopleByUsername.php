<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Directory;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'people')]
class PeopleByUsername extends AbstractPeople
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    #[Groups(['people'])]
    public string $username;

    #[ORM\Column(type: 'integer')]
    protected int $id;

    public function getUsername(): string
    {
        return $this->username;
    }
}
