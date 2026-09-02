<?php

declare(strict_types=1);

namespace App\Controller\MIS\ThirdPartyApp;

use App\Entity\Directory\People;
use App\Manager\MIS\Module\ThirdPartyManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SyncUpdateTaskController extends AbstractController
{
    public function __construct(
        private readonly ThirdPartyManager $manager,
    ) {
    }

    public function __invoke(#[MapEntity(id: 'userId')] People $user)
    {
        $this->manager->createGrantAccessTasksByUser($user);
        $this->manager->createRemoveAccessTasksByUser($user);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
