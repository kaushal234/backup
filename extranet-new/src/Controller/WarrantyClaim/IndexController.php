<?php

declare(strict_types=1);

namespace App\Controller\WarrantyClaim;

use App\CQRS\Query\WarrantyClaim\FindAllWarrantyClaimsQuery;
use App\DataTable\Type\WarrantyClaim\WarrantyClaimDataTableType;
use App\Http\Responder;
use App\Sdk\Resource\Customer;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/warranty_claims')]
class IndexController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    #[Route(path: '', name: 'warranty_claim:index', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request): Response
    {
        /** @var Customer|null $activeCustomer */
        $activeCustomer = $request->getSession()->get('customer');

        $query = new FindAllWarrantyClaimsQuery(options: ['customerName' => $activeCustomer?->name]);
        $datatable = $this->createDataTable(WarrantyClaimDataTableType::class, $query);

        $datatable->handleRequest($request);
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return $this->responder->render('warranty_claims/index.html.twig',
            [
                'datatable' => $datatable->createView(),
            ]
        );
    }
}
