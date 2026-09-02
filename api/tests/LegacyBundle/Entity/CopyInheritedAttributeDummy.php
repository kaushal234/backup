<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Entity;

use LegacyBundle\Doctrine\Mapping\Attributes\Copy;

class CopyInheritedAttributeDummy extends CopyAttributeDummy
{
    #[Copy(table: 'dummy_people_inherited', columns: ['name_inherited'])]
    protected string $dummyPeche;
}
