<?php

declare(strict_types=1);

namespace App\Controller\MIS\ThirdPartyApp;

use App\Dto\MIS\Module\UserListThirdPartyApp;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MoveFromBlacklistController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UserListThirdPartyApp $data): Extended
    {
        $user = $data->user;
        $thirdPartyApp = $data->module;

        if ($thirdPartyApp->isBlacklisted($user)) {
            $thirdPartyApp->removeBlacklistedUser($user);
            $this->entityManager->flush();
        }

        return $thirdPartyApp;
    }
}
