<?php

declare(strict_types=1);

namespace AppBundle\Controller\Chat;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/chat')]
class HomeController
{
    #[Route('', name: 'chat_home', methods: ['GET', 'POST'])]
    #[Template(template: 'chat/index.html.twig')]
    public function __invoke(): array
    {
        return [];
    }
}
