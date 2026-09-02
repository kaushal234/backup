<?php

declare(strict_types=1);

namespace App\Controller\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUser;
use App\Manager\Sales\ExtranetUserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExtranetUserArchiveAccountController extends AbstractController
{
    private readonly ExtranetUserManager $extranetUserManager;

    public function __construct(ExtranetUserManager $extranetUserManager)
    {
        $this->extranetUserManager = $extranetUserManager;
    }

    public function __invoke(ExtranetUser $extranetUser)
    {
        if ($extranetUser->getExtranetUserProfile()->archived) {
            return $extranetUser;
        }

        return $this->extranetUserManager->disableContact($extranetUser, true);
    }
}
