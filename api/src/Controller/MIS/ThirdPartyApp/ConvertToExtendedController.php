<?php

declare(strict_types=1);

namespace App\Controller\MIS\ThirdPartyApp;

use App\Entity\Module\Module;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConvertToExtendedController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Module $module): Module
    {
        $this->entityManager->getRepository(Module::class)->convertType($module, Module::TYPE_THIRD_PARTY_APP_EXTENDED);

        $this->entityManager->clear();

        return $this->entityManager->getRepository(Module::class)->find($module->getId());
    }
}
