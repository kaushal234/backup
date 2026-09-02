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
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/manufacturing/bill-of-material-drawing-3d-files')]
final class BillOfMaterialDrawing3DFilesController
{
    public function __construct(
        private readonly Downloader $downloader,
        private readonly Responder $responder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/{site}/{item}/{effectiveDate}/{revision}', name: 'manufacturing:bill-of-material:drawing-3d', defaults: ['revision' => null], methods: [Request::METHOD_GET])]
    public function __invoke(Request $request, RouterInterface $router, int $site, string $item, ?string $effectiveDate, ?string $revision): Response
    {
        try {
            return $this->downloader->download(BillOfMaterials::class, ['item' => $item, 'site' => $site, 'effectiveDate' => $effectiveDate, 'use3dFiles' => true]);
        } catch (NotFoundHttpException) {
            $this->responder->flash('danger', $this->translator->trans('manufacturing.download_errors.3d_part_files', ['%part%' => $item, '%revision%' => $revision ?: 'N/A', '%site%' => $site], 'manufacturing'));

            $previousPage = $request->headers->get('referer');
            if (!empty($previousPage)) {
                return new RedirectResponse($previousPage);
            }

            return new RedirectResponse($router->generate('index'));
        }
    }
}
