<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/sample-form')]
class SampleFormController extends AbstractController
{
    #[Route(path: '', name: 'sample_form_home', methods: ['GET'])]
    #[Template('sample_form.html.twig')]
    public function search()
    {
        return [];
    }
}
