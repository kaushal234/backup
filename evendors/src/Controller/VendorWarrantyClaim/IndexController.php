<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim;

use App\CQRS\Query\VendorWarrantyClaim\FindAllVendorWarrantyClaimsQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendor-warranty-claim')]
final class IndexController
{
    public function __construct(
        private readonly QueryBusInterface $queries,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/', name: 'vendor-warranty-claim:index', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        $claims = $this->queries->dispatch(new FindAllVendorWarrantyClaimsQuery());

        return $this->responder->render('vendor-warranty-claim/index.html.twig', [
            'claims' => $claims,
        ]);
    }
}
