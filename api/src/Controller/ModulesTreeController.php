<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Module\Module;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModulesTreeController extends AbstractController
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke()
    {
        $modules = $this->entityManager->getRepository(Module::class)->findAll();

        $orphans = [];
        foreach ($modules as $module) {
            if ($module->getRequiredModules()->isEmpty()) {
                $orphans[] = $module;
            }
        }

        return $orphans;
    }
}
