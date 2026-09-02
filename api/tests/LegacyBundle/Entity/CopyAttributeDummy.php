<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Entity;

use LegacyBundle\Doctrine\Mapping\Attributes\Copy;

class CopyAttributeDummy
{
    #[Copy(table: 'dummy_people', columns: ['name'])]
    protected string $dummyPeche;

    #[Copy(table: 'dummy_people', columns: ['email'])]
    #[Copy(table: 'dummy_user', columns: ['username'])]
    protected string $dummyMoore;

    public function __construct(string $dummyPeche, string $dummyMoore)
    {
        $this->dummyPeche = $dummyPeche;
        $this->dummyMoore = $dummyMoore;
    }
}
