<?php

declare(strict_types=1);

namespace App\Controller\Forecast;

use App\CQRS\Query\MaterialRequirementsPlanning\FindAllMaterialRequirementsPlanningsGroupedByMonthQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forecast')]
final class MonthlyController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $bus,
    ) {
    }

    #[Route('/monthly', name: 'forecast:monthly', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request): Response
    {
        $materialRequirementsPlannings = $this->bus->dispatch(new FindAllMaterialRequirementsPlanningsGroupedByMonthQuery());

        return $this->responder->render('forecast/monthly.html.twig', [
            'plannings' => $materialRequirementsPlannings,
        ]);
    }
}
