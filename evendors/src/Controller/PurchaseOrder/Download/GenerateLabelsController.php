<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder\Download;

use App\CQRS\Query\PurchaseOrder\FindOnePurchaseOrderQuery;
use App\CQRS\QueryBusInterface;
use App\Form\PurchaseOrder\GenerateLabelsType;
use App\Http\Responder;
use App\Sdk\Resource\PurchaseOrder;
use App\Sdk\TokenProvider\TokenProviderInterface;
use Exception;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/purchase-order')]
final class GenerateLabelsController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
        private readonly FormFactoryInterface $factory,
        private readonly TranslatorInterface $translator,
        private readonly HttpClientInterface $internalApiClient,
        private readonly TokenProviderInterface $tokenProvider,
    ) {
    }

    #[Route('/generate_labels/{id}/{erp}', name: 'purchase-order:generate_labels', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request, int|string $id, ?int $erp = null): Response|StreamedResponse
    {
        try {
            /** @var PurchaseOrder $order */
            $order = $this->queryBus->dispatch(new FindOnePurchaseOrderQuery($id, $erp));
        } catch (HandlerFailedException $e) {
            if (Response::HTTP_NOT_FOUND === $e->getCode()) {
                throw new NotFoundHttpException(previous: $e);
            }
            throw $e;
        }

        $form = $this->factory->createNamed(GenerateLabelsType::createName($order), GenerateLabelsType::class, $order);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $token = $this->tokenProvider->getToken();

                /** @var PurchaseOrder $data */
                $data = $form->getData();
                $file = $this->internalApiClient->request(Request::METHOD_PUT, '/ion/purchase_orders/'.$order->id.'/pdf_labels', [
                    'auth_bearer' => $token,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/pdf',
                    ],
                    'json' => ['lines' => $data->lines],
                ]);
                $response = new StreamedResponse(static function () use ($file): void {
                    echo $file->getContent();
                });

                $response->headers->set('Content-Disposition', $file->getHeaders()['content-disposition']);
                $response->headers->set('Content-type', $file->getHeaders()['content-type']);

                return $response;
            }

            foreach ($form->getErrors(true) as $error) {
                $this->responder->flash('danger', $this->translator->trans($error->getMessage()));
            }
        } catch (HandlerFailedException $exception) {
            $this->responder->flash('danger', 'purchase_order.error');
        } catch (Exception $exception) {
            $this->responder->flash('danger', 'internal error');
        }

        return $this->responder->route('purchase-order:show', ['id' => $id, 'erp' => $erp]);
    }
}
