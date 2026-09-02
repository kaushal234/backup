<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\Project;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mis/projects/chart')]
class ChartController extends AbstractController
{
    #[Route(path: '', name: 'project_chart_home', methods: ['GET'])]
    #[Template('mis/project/chart.html.twig')]
    public function home()
    {
        return [];
    }
}
