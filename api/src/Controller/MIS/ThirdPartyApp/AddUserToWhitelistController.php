<?php

declare(strict_types=1);

namespace App\Controller\MIS\ThirdPartyApp;

use App\Dto\MIS\Module\UserListThirdPartyApp;
use App\Entity\Module\ThirdPartyApp\BusinessUnitPosition;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Manager\MIS\Module\ThirdPartyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddUserToWhitelistController extends AbstractController
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

        if ($thirdPartyApp->isBlacklisted($user)) {
            throw new BadRequestHttpException('User is blacklisted');
        }

        $businessUnitPosition = $this->entityManager->getRepository(BusinessUnitPosition::class)->findOneBy([
            'thirdPartyApp' => $thirdPartyApp,
            'businessUnit' => $user->getBusinessUnit(),
            'position' => $user->getPosition(),
        ]);
        if ($businessUnitPosition) {
            throw new BadRequestHttpException('User is already in business unit/position configuration');
        }

        $thirdPartyApp->addWhitelistedUser($user);
        $this->entityManager->flush();

        $this->thirdPartyManager->createGrantAccessTaskByWhitelistUser($thirdPartyApp, $user);

        return $thirdPartyApp;
    }
}
