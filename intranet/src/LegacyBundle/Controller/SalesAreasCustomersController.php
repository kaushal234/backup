<?php

declare(strict_types=1);

namespace LegacyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class SalesAreasCustomersController extends AbstractController
{
    #[Route(path: '/sales_service/salesareas/salesareas_admin.php', name: 'legacy_salesareas', methods: 'GET|POST')]
    public function router(): RedirectResponse
    {
        return $this->redirectToRoute('sales_areas_home');
    }
}
