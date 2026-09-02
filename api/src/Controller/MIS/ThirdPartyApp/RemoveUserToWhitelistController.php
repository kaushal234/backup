<?php

declare(strict_types=1);

namespace App\Controller\MIS\ThirdPartyApp;

use App\Dto\MIS\Module\UserListThirdPartyApp;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Manager\MIS\Module\ThirdPartyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RemoveUserToWhitelistController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ThirdPartyManager $thirdPartyManager,
    ) {
    }

    public function __invoke(UserListThirdPartyApp $data): Extended
    {
        $user = $data->user;
        $thirdPartyApp = $data->module;

        $thirdPartyApp->removeWhitelistedUser($user);
        $this->entityManager->flush();

        $this->thirdPartyManager->createRemoveAccessTaskByWhitelistedUser($thirdPartyApp, $user);

        return $thirdPartyApp;
    }
}
