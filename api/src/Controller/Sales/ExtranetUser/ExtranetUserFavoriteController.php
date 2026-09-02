<?php

declare(strict_types=1);

namespace App\Controller\Sales\ExtranetUser;

use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserFavorite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;

class ExtranetUserFavoriteController extends AbstractController
{
    private readonly Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke($data): ExtranetUserFavorite
    {
        $user = $this->security->getUser();
        if (
            ($user instanceof People && !$this->security->isGranted('FEATURE_EXTRANET_USER_EDIT', $data))
            || ($user instanceof ExtranetUser && $user->getUserIdentifier() !== $data->getExtranetUser()->getUserIdentifier())
        ) {
            throw $this->createAccessDeniedException();
        }

        return $data;
    }
}
