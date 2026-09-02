<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SwitchInController extends AbstractController
{
    private readonly UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function __invoke(User $userTarget)
    {
        $newToken = $this->userManager->generateSwitchToUserToken($userTarget);

        return new JsonResponse(['token' => $newToken], Response::HTTP_CREATED);
    }
}
