<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'demos_files')]
#[App\Loggable(owner: 'demo', ownerRelation: 'demoFiles')]
class DemoFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Demo', inversedBy: 'demoFiles')]
    private ?Demo $demo = null;

    public function getDemo(): Demo
    {
        return $this->demo;
    }

    public function setDemo(Demo $demo): self
    {
        $this->demo = $demo;

        return $this;
    }
}
