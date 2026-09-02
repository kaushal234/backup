<?php

declare(strict_types=1);

namespace App\Postman\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Script
{
    public Collection $exec;
    public string $type;

    public function __construct()
    {
        $this->exec = new ArrayCollection();
    }

    public function addExec(string $exec): void
    {
        $this->exec->add($exec);
    }
}
