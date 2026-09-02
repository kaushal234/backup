<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * StaticPageController.
 *
 * Renders content-only pages that have no dynamic data: each action just renders
 * a template.
 *
 * Route names match those referenced in base.html.twig and the page templates:
 *   page:major_repairs, page:warranty_conditions,
 *   page:parts_terms, page:parts_returns, page:rspl, page:telemetry,
 *   page:trainings, page:technical_services, page:coming_soon, page:accessories
 */
final class StaticPageController extends AbstractController
{
    #[Route('/services/technical-services', name: 'page:technical_services')]
    public function technicalServices(): Response
    {
        return $this->render('pages/technical_services.html.twig');
    }

    #[Route('/services/trainings', name: 'page:trainings')]
    public function trainings(): Response
    {
        return $this->render('pages/technical_trainings.html.twig');
    }

    #[Route('/services/major-components-repairs', name: 'page:major_repairs')]
    public function majorRepairs(): Response
    {
        return $this->render('pages/major_components_repairs.html.twig');
    }

    #[Route('/services/warranty-conditions', name: 'page:warranty_conditions')]
    public function warrantyConditions(): Response
    {
        return $this->render('pages/warranty_conditions.html.twig');
    }

    #[Route('/services/telemetry', name: 'page:telemetry')]
    public function telemetry(): Response
    {
        return $this->render('pages/monitor_fleet_telemetry.html.twig');
    }

    #[Route('/parts/terms-and-conditions', name: 'page:parts_terms')]
    public function partsTerms(): Response
    {
        return $this->render('pages/parts_terms_conditions.html.twig');
    }

    #[Route('/parts/return-instructions', name: 'page:parts_returns')]
    public function partsReturns(): Response
    {
        return $this->render('pages/parts_return_instructions.html.twig');
    }

    #[Route('/parts/recommended-spare-parts-list', name: 'page:rspl')]
    public function rspl(): Response
    {
        return $this->render('pages/recommended_spare_parts_list.html.twig');
    }

    #[Route('/coming-soon', name: 'page:coming_soon')]
    public function comingSoon(): Response
    {
        return $this->render('pages/coming_soon.html.twig');
    }

    #[Route('/parts/accessories', name: 'page:accessories')]
    public function accessories(): Response
    {
        return $this->render('pages/accessories.html.twig');
    }
}
