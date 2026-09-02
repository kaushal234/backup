<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim;

use App\CQRS\Query\VendorWarrantyClaim\FindWCVendorWarrantyClaimQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendor-warranty-claim')]
final class ShowWCController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/wc/show/{id}', name: 'vendor-warranty-claim:show-wc', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request, string $id): Response
    {
        $claim = $this->queryBus->dispatch(new FindWCVendorWarrantyClaimQuery($id));

        return $this->responder->render('vendor-warranty-claim/show.html.twig', [
            'claim' => $claim,
            'moduleDisplay' => $request->query->get('moduleDisplay'),
        ]);
    }
}
