<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Http\Responder;
use App\Security\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[AsController]
#[Route('/security')]
class LoginController
{
    public function __construct(
        private readonly Security $security,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/login', name: 'security:login', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->security->isFullyAuthenticated()) {
            return $this->responder->route('index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->responder->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }
}
