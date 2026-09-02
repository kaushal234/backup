<?php

declare(strict_types=1);

namespace App\Controller\RequestForQuotation;

use App\Http\Responder;
use App\Sdk\ClientInterface;
use App\Sdk\Downloader;
use App\Sdk\Resource\RequestForQuotation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/request-for-quotation')]
final class DownloadController
{
    public function __construct(
        private readonly Downloader $downloader,
        private readonly ClientInterface $client,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/download/{id}/{erp}', name: 'request-for-quotation:download', methods: [Request::METHOD_GET])]
    public function __invoke(int|string $id, ?int $erp = null): Response
    {
        if (null === $erp) {
            $request = $this->client->find(RequestForQuotation::class, ['id' => (string) $id]);
        } else {
            $request = $this->client->find(RequestForQuotation::class, ['id' => (string) $id, 'erp' => (string) $erp]);
        }

        try {
            $response = $this->downloader->download(RequestForQuotation::class, ['id' => $id, 'iri' => $request->iri]);
            if (0 === $response->getFile()->getSize()) {
                $this->responder->flash('danger', 'file.empty.request_for_quotation');

                return $this->responder->render('request-for-quotation/show.html.twig', [
                    'request' => $request,
                ]);
            }

            return $response;
        } catch (HandlerFailedException $exception) {
            $this->responder->flash('danger', array_values($exception->getWrappedExceptions())[0]->getMessage());

            return $this->responder->render('request-for-quotation/show.html.twig', [
                'request' => $request,
            ]);
        }
    }
}
