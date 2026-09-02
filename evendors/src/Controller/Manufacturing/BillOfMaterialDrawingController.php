<?php

declare(strict_types=1);

namespace App\Controller\Manufacturing;

use App\Http\Responder;
use App\Sdk\Downloader;
use App\Sdk\Resource\Manufacturing\BillOfMaterials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

#[Route('/manufacturing/bill-of-material-drawing')]
final class BillOfMaterialDrawingController
{
    public function __construct(
        private readonly Downloader $downloader,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/{site}/{item}/{effectiveDate}', name: 'manufacturing:bill-of-material:drawing', methods: [Request::METHOD_GET])]
    public function __invoke(RouterInterface $router, ?int $site, string $item, ?string $effectiveDate = ''): Response
    {
        try {
            return $this->downloader->download(BillOfMaterials::class, ['item' => $item, 'site' => $site, 'effectiveDate' => $effectiveDate]);
        } catch (NotFoundHttpException) {
            $this->responder->flash('danger', 'file.empty.drawing');

            return new RedirectResponse($router->generate('manufacturing:bill-of-material', ['site' => $site, 'item' => $item, 'effectiveDate' => $effectiveDate]));
        }
    }
}
