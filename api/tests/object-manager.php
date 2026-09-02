<?php

declare(strict_types=1);

use App\Kernel;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ObjectManager;

require __DIR__.'/../config/bootstrap.php';

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');

$metadataFactory = new class($doctrine) implements ClassMetadataFactory {
    private readonly Registry $doctrine;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getAllMetadata()
    {
        $all = [];

        foreach ($this->doctrine->getManagers() as $manager) {
            $all = array_merge($all, $manager->getMetadataFactory()->getAllMetadata());
        }

        return $all;
    }

    public function getMetadataFor($className)
    {
        return $this->doctrine->getManagerForClass($className)->getClassMetadata($className);
    }

    public function isTransient($className)
    {
        $isTransient = true;

        foreach ($this->doctrine->getManagers() as $manager) {
            $isTransient = $isTransient && $manager->getMetadataFactory()->isTransient($className);
        }

        return $isTransient;
    }

    public function hasMetadataFor($className)
    {
        $hasMetadata = false;

        foreach ($this->doctrine->getManagers() as $manager) {
            $hasMetadata = $hasMetadata || $manager->getMetadataFactory()->hasMetadataFor($className);
        }

        return $hasMetadata;
    }

    public function setMetadataFor($className, $class)
    {
        throw new Exception(__FILE__);
    }
};

return new class($doctrine, $metadataFactory) implements ObjectManager {
    private readonly Registry $doctrine;
    private readonly ClassMetadataFactory $metadataFactory;

    public function __construct(Registry $doctrine, ClassMetadataFactory $metadataFactory)
    {
        $this->doctrine = $doctrine;
        $this->metadataFactory = $metadataFactory;
    }

    public function getRepository($className)
    {
        return $this->doctrine->getRepository($className);
    }

    public function getClassMetadata($className)
    {
        return $this->doctrine->getManagerForClass($className)->getClassMetadata($className);
    }

    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    public function find($className, $id)
    {
        throw new Exception(__FILE__);
    }

    public function persist($object)
    {
        throw new Exception(__FILE__);
    }

    public function remove($object)
    {
        throw new Exception(__FILE__);
    }

    public function merge($object): never
    {
        throw new Exception(__FILE__);
    }

    public function clear($objectName = null)
    {
        throw new Exception(__FILE__);
    }

    public function detach($object)
    {
        throw new Exception(__FILE__);
    }

    public function refresh($object)
    {
        throw new Exception(__FILE__);
    }

    public function flush()
    {
        throw new Exception(__FILE__);
    }

    public function initializeObject($obj)
    {
        throw new Exception(__FILE__);
    }

    public function contains($object)
    {
        throw new Exception(__FILE__);
    }
};
