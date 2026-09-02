<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthController
{
    #[Route(path: '/check', methods: 'GET')]
    public function check(): Response
    {
        return new Response('OK');
    }
}
