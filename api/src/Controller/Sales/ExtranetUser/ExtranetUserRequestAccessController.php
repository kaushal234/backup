<?php

declare(strict_types=1);

namespace App\Controller\Sales\ExtranetUser;

use App\Entity\Sales\ExtranetUser;
use App\Manager\Sales\ExtranetUserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ExtranetUserRequestAccessController extends AbstractController
{
    private readonly ExtranetUserManager $extranetUserManager;

    public function __construct(ExtranetUserManager $extranetUserManager)
    {
        $this->extranetUserManager = $extranetUserManager;
    }

    public function __invoke(ExtranetUser $extranetUser)
    {
        try {
            $this->extranetUserManager->generateSequence($extranetUser);
        } catch (\LogicException $logicException) {
            throw new ConflictHttpException($logicException->getMessage(), $logicException);
        }

        return $extranetUser;
    }
}
