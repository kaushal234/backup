<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\EventListener\JWTAuthenticationSuccessListener;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SwitchOutController extends AbstractController
{
    private readonly UserManager $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function __invoke(Request $request)
    {
        $jwtTokenPayload = $request->attributes->get(JWTAuthenticationSuccessListener::RAW_JWT_ATTRIBUTE, []);
        $newToken = $this->userManager->generateExitSwitching($jwtTokenPayload);

        return new JsonResponse(['token' => $newToken]);
    }
}
