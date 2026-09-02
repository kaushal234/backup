<?php

declare(strict_types=1);

namespace App\Repository\Directory;

use App\Entity\Directory\Network;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NetworkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Network::class);
    }

    public function findNetworkByName(string $name): Network
    {
        $network = $this->findOneBy(['name' => $name]);

        if (!$network instanceof Network) {
            throw new \Exception(\sprintf("Couldn't find %s network", $name));
        }

        return $network;
    }
}
