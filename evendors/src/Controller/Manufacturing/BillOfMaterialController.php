<?php

declare(strict_types=1);

namespace App\Controller\Manufacturing;

use App\CQRS\Query\Manufacturing\FindBillOfMaterialQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/manufacturing/bill-of-material')]
final class BillOfMaterialController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $bus,
    ) {
    }

    #[Route('/{site}/{item}/{effectiveDate}', name: 'manufacturing:bill-of-material', methods: [Request::METHOD_GET])]
    public function __invoke(?int $site, string $item, string $effectiveDate): Response
    {
        $billOfMaterials = $this->bus->dispatch(new FindBillOfMaterialQuery($item, $site, $effectiveDate));

        return $this->responder->render('manufacturing/index.html.twig', [
            'billOfMaterials' => $billOfMaterials,
        ]);
    }
}
