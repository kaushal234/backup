<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/sample-data-table')]
class SampleDataTableController extends AbstractController
{
    #[Route(path: '', name: 'sample_data_table_home', methods: ['GET'])]
    #[Template('sample_data_table.html.twig')]
    public function search()
    {
        return [];
    }
}
