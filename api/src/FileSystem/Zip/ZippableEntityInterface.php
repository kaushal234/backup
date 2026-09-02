<?php

declare(strict_types=1);

namespace App\FileSystem\Zip;

use Doctrine\Common\Collections\Collection;

interface ZippableEntityInterface
{
    public function getZippableFiles(): Collection;
}
