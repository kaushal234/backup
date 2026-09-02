<?php

declare(strict_types=1);

namespace AppBundle\Controller\Corporate;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CorporateController extends AbstractController
{
    #[Route(path: '/corporate', name: 'corporate_pages_main', methods: ['GET'])]
    #[Template('corporate/pages/corporate.html.twig')]
    public function corporate()
    {
        return [];
    }

    #[Route(path: '/corporate/ethics', name: 'corporate_pages_ethics', methods: ['GET'])]
    #[Template('corporate/pages/ethics.html.twig')]
    public function ethics()
    {
        return [];
    }
}
