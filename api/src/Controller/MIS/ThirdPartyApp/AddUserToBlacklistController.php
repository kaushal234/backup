<?php

declare(strict_types=1);

namespace App\Controller\MIS\ThirdPartyApp;

use App\Dto\MIS\Module\UserListThirdPartyApp;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Manager\MIS\Module\ThirdPartyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddUserToBlacklistController extends AbstractController
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

        if ($thirdPartyApp->isWhitelisted($user)) {
            throw new BadRequestHttpException('User is whitelisted');
        }

        $thirdPartyApp->addBlacklistedUser($user);
        $this->entityManager->flush();

        $this->thirdPartyManager->createRemoveAccessTaskByBlacklistUser($thirdPartyApp, $user);

        return $thirdPartyApp;
    }
}
