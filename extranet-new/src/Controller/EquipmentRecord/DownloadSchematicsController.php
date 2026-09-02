<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\Http\Responder;
use App\Sdk\Downloader;
use App\Sdk\Resource\EquipmentSerial;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

#[AsController]
#[Route('/equipments')]
final class DownloadSchematicsController
{
    public function __construct(
        private readonly Downloader $downloader,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/download/{project}/{signalCode}/{erp}/{serial}', name: 'schematics:download', methods: [Request::METHOD_GET])]
    public function __invoke(string $project, string $signalCode, int $erp, string $serial, Request $request, #[MapQueryParameter] ?string $filename = null): BinaryFileResponse|RedirectResponse
    {
        try {
            return $this->downloader->download(EquipmentSerial::class, ['project' => $project, 'signalCode' => $signalCode, 'site' => $erp, 'product' => $serial], $filename);
        } catch (ClientExceptionInterface|NotFoundHttpException $exception) {
            $details = \sprintf(' (project: %s, signalCode: %s, erp: %d, serial: %s)',
                $project,
                $signalCode,
                $erp,
                $serial
            );
            $this->responder->flash('danger', $exception->getMessage().$details);
        }

        $referer = $request->headers->get('referer');

        return $this->responder->redirect($referer);
    }
}
