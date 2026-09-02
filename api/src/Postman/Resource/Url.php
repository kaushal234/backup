<?php

declare(strict_types=1);

namespace App\Postman\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Url
{
    public string $raw;
    public Collection $host;
    public Collection $path;

    public function __construct()
    {
        $this->host = new ArrayCollection();
        $this->path = new ArrayCollection();
    }

    public function addHost(string $host): void
    {
        $this->host->add($host);
    }

    public function addPath(string $path): void
    {
        $this->path->add($path);
    }
}
