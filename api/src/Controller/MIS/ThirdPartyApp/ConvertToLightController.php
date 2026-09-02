<?php

declare(strict_types=1);

namespace App\Controller\MIS\ThirdPartyApp;

use App\Entity\Module\Module;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Entity\Module\ThirdPartyApp\UpdateTask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConvertToLightController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Module $module): Module
    {
        if ($module instanceof Extended) {
            $this->entityManager->getRepository(UpdateTask::class)->deleteAllByModule($module);
        }

        $this->entityManager->getRepository(Module::class)->convertType($module, Module::TYPE_THIRD_PARTY_APP_LIGHT);

        $this->entityManager->clear();

        return $this->entityManager->getRepository(Module::class)->find($module->getId());
    }
}
