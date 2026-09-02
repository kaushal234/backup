<?php

declare(strict_types=1);

namespace App\Controller\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/security')]
final class LogoutController
{
    #[Route('/logout', name: 'security:logout', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
    }
}
