<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Sales\OrderDataTableType;
use AppBundle\Form\Type\Common\FileEditDescriptionType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\LegacyIdSearchType;
use AppBundle\Form\Type\Sales\Order\ConfirmOrderType;
use AppBundle\Form\Type\Sales\Order\OrderType;
use AppBundle\Manager\Sales\OrderManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security as SecurityService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/orders', defaults: ['alvest_module' => 'SOR', 'moduleDomain' => 'sales_orders'])]
#[IsGranted('FEATURE_SALES_ORDER_READ')]
class OrderController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
        private readonly FormFactoryInterface $formFactory,
        private readonly ViolationMapper $violationMapper,
        private readonly OrderManager $orderManager,
        private readonly SecurityService $security,
        private readonly FileStreamedResponseFactory $fileStreamedResponseFactory,
    ) {
    }

    #[Route(path: '', name: 'sales_orders_home', methods: 'GET|POST')]
    #[Template('sales/orders/home.html.twig')]
    public function home(Request $request)
    {
        $quickAccess = $this->createForm(IdSearchType::class, null, ['id_label' => false, 'id_placeholder' => 'by_id']);
        $quickAccess->handleRequest($request);
        if ($quickAccess->isSubmitted() && $quickAccess->isValid()) {
            $id = $quickAccess->get('id')->getData();
            try {
                $this->client->get(\sprintf('sales/orders/%s', $id));

                return $this->redirectToRoute('sales_orders_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('Sales order #%s does not exist.', $id));
            }
        }
        $quickAccessLegacy = $this->createForm(LegacyIdSearchType::class, null, ['legacy_id_label' => false, 'legacy_id_placeholder' => 'By Legacy ID']);
        $quickAccessLegacy->handleRequest($request);
        if ($quickAccessLegacy->isSubmitted() && $quickAccessLegacy->isValid()) {
            $legacyId = $quickAccessLegacy->get('legacyId')->getData();
            try {
                $order = $this->client->findOneBy('sales/orders', ['legacyId' => $legacyId]);

                return $this->redirectToRoute('sales_orders_show', ['id' => Iri::id($order)]);
            } catch (\RangeException $e) {
                $this->addFlash('error', \sprintf('Sales order with legacy id #%s does not exist.', $legacyId));
            }
        }

        $datatable = $this->createDataTable(OrderDataTableType::class, OrderDataTableType::RESOURCE);
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'quickAccess' => $quickAccess->createView(),
            'quickAccessLegacy' => $quickAccessLegacy->createView(),
            'orderDatatable' => $datatable->createView(),
            'ordersBySphByStatusReport' => $this->client->get('reports/resource=/sales/orders;x=sso.name;y=status'),
        ];
    }

    #[Route(path: '/{id}/show', name: 'sales_orders_show', requirements: ['id' => '\d+'], methods: 'GET')]
    #[Template('sales/orders/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders'])] ApiData $order)
    {
        return ['order' => $order];
    }

    #[Route(path: '/add', name: 'sales_orders_add', methods: 'GET|POST')]
    #[Template('sales/orders/add_edit.html.twig')]
    #[IsGranted('FEATURE_SALES_ORDER_CREATE')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('order', OrderType::class, ['customerPurchaseOrders' => ['']]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $order = $this->client->save('sales/orders', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('sales_order.messages.success.create', [], 'sales_orders')
                );

                return $this->redirectToRoute('sales_orders_show', ['id' => $order['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return ['form' => $form->createView()];
    }

    #[Route(path: '/{id}/duplicate', name: 'sales_orders_duplicate', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('sales/orders/duplicate.html.twig')]
    #[IsGranted('FEATURE_SALES_ORDER_CREATE')]
    public function duplicate(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders'])] ApiData $order, Request $request)
    {
        $form = $this->createForm(ConfirmOrderType::class, $order);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $id = $order['id'];
            try {
                $duplicate = $this->client->post(\sprintf('%s/duplicate', $order->getIri()));

                $id = $duplicate['id'];
                $this->addFlash(
                    'success',
                    $this->translator->trans('sales_order.messages.success.create', [], 'sales_orders')
                );
            } catch (ClientException $e) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('sales_order.messages.errors.duplicate', [], 'sales_orders')
                );
            } finally {
                return $this->redirectToRoute('sales_orders_show', ['id' => $id]);
            }
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'sales_orders_edit', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('sales/orders/add_edit.html.twig')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders'])] ApiData $order, Request $request)
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (!$this->security->isGranted('FEATURE_SALES_ORDER_EDIT') && !('PENDING' === $order['status'] && $user->getId() === Iri::id($order['asm']) && $this->security->isGranted('ACL_ROLE_ASM'))) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData()->toArray();
                unset($data['customerName']);

                $this->client->save('sales/orders', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('sales_order.messages.success.edit', [], 'sales_orders')
                );

                return $this->redirectToRoute('sales_orders_show', ['id' => $order['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'sales_orders_delete', requirements: ['id' => '\d+'], methods: 'GET|POST')]
    #[Template('sales/orders/delete.html.twig')]
    #[IsGranted('FEATURE_SALES_ORDER_EDIT')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders'])] ApiData $order, Request $request, $id)
    {
        $form = $this->formFactory->createNamed('app_order_delete', ConfirmOrderType::class, $order);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->remove('sales/orders', $id);

                $this->addFlash(
                    'success',
                    $this->translator->trans('sales_order.messages.success.delete', [], 'sales_orders')
                );

                return $this->redirectToRoute('sales_orders_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/logs', name: 'sales_orders_logs', requirements: ['id' => '\d+'], methods: 'GET')]
    #[Template('sales/orders/logs.html.twig')]
    public function logs(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders'])] ApiData $order)
    {
        return ['order' => $order];
    }

    #[Route(path: '/{id}/tasks', name: 'sales_orders_tasks', requirements: ['id' => '\d+'], methods: 'GET')]
    #[Template('sales/orders/tasks.html.twig')]
    public function tasks(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders'])] ApiData $order)
    {
        return ['order' => $order];
    }

    #[Route(path: '/{id}/files', name: 'sales_orders_files', requirements: ['id' => '\d+'], methods: 'GET')]
    #[Template('sales/orders/files.html.twig')]
    public function files(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders'])] ApiData $order)
    {
        return ['order' => $order];
    }

    #[Route(path: '/{id}/files_ajax', name: 'sales_orders_files_ajax', requirements: ['id' => '\d+'], methods: 'GET', condition: 'request.isXmlHttpRequest()')]
    #[Template('sales/orders/files_ajax.html.twig')]
    public function orderFilesAjax(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders'])] ApiData $order)
    {
        return ['order' => $order];
    }

    /**
     * @return StreamedResponse
     */
    #[Route(path: '/{orderId}/files/{id}', name: 'sales_orders_files_show', requirements: ['id' => '\d+', 'orderId' => '\d+'], methods: 'GET')]
    public function showFile($orderId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('sales/orders/%s/files/%s', $orderId, $id));
    }

    #[Route(path: '/{orderId}/files/{id}/edit', name: 'sales_orders_files_edit', requirements: ['id' => '\d+', 'orderId' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('FEATURE_SALES_ORDER_CREATE')]
    public function editFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/orders', 'id' => 'orderId'])] ApiData $order, int $id): Response
    {
        $file = array_filter($order['files'], static function (array $orderFile) use ($id) {
            return $id === $orderFile['id'];
        });
        if (1 !== \count($file)) {
            $this->redirectToRoute('sales_orders_files', ['id' => $order->getIriId()]);
        }
        $file = current($file);
        $form = $this->container->get('form.factory')->createNamed('editFile', FileEditDescriptionType::class, $file);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $this->client->save('files', [
                    '@id' => \sprintf('/files/%d', $id),
                    'description' => $data['description'],
                ]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }

            $this->addFlash(
                'success',
                $this->translator->trans('customers.messages.success.file_description', [], 'sales_customers')
            );

            return $this->redirectToRoute('sales_orders_files', ['id' => $order->getIriId()]);
        }

        return $this->render('sales/orders/files_edition.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{orderId}/files/{id}/delete', name: 'sales_orders_file_delete', requirements: ['id' => '\d+', 'orderId' => '\d+'], methods: ['GET'])]
    public function deleteFile(Request $request, $orderId, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_order_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('sales_orders_files', ['id' => $orderId]);
        }

        try {
            $this->client->request('sales/orders', $orderId, \sprintf('files/%s', $id), Request::METHOD_DELETE);
            $this->addFlash('success', $this->translator->trans('sales_order.messages.success.delete_file', [], 'sales_orders'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('sales_order.messages.errors.delete_file', [], 'sales_orders'));
        }

        return $this->redirectToRoute('sales_orders_files', ['id' => $orderId]);
    }

    #[Route(path: '/transfer/{quoteNumber}', name: 'sales_orders_transfer', methods: ['GET|POST'])]
    #[Template('sales/orders/add_edit.html.twig')]
    public function transfer(Request $request, ?string $quoteNumber = null): array|RedirectResponse
    {
        try {
            $order = $this->orderManager->buildOrder($request);
        } catch (\RangeException) {
            $this->addFlash('error', 'Impossible to retrieve equote data.');

            return $this->redirectToRoute('sales_orders_home');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('sales_orders_home');
        }

        $orderAlreadyExist = false;
        try {
            $existingOrders = $this->client->findBy('sales/orders', ['equoteId' => $quoteNumber]);
            $orderAlreadyExist = $existingOrders->count() >= 1;
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        $form = $this->createForm(OrderType::class, new ApiData($order));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $order = $this->client->save('sales/orders', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('sales_order.messages.success.create', [], 'sales_orders')
                );

                if ($request->hasSession() && $request->getSession()->has('_legacy_equote_data')) {
                    $request->getSession()->remove('_legacy_equote_data');
                    // hack to allow the workflow to continue in the legacy to be removed at SOL migration
                    $_SESSION['sess']['sor']['equote']['header']['sorid'] = $order['legacyId'];

                    return $this->redirectToRoute('legacy_sales', ['m' => ['sor', 'view', 'transfer_legacy'], 'id' => $order['legacyId']]);
                }

                return $this->redirectToRoute('legacy_sales', ['m' => ['sor', 'view', 'transfer'], 'id' => $order['legacyId']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'orderAlreadyExist' => $orderAlreadyExist,
        ];
    }

    #[Route(path: '/{id}/status/{status}', name: 'sales_orders_status', requirements: ['id' => '\d+', 'status' => 'IN PROGRESS|CLOSED'], methods: 'GET')]
    #[IsGranted('FEATURE_SALES_ORDER_STATUS_UPDATE')]
    public function status($id, $status): RedirectResponse
    {
        // Fetching $order allow us to throw 404 before the PUT
        try {
            $this->client->put(\sprintf('sales/orders/%d/status', $id), [
                'json' => [
                    'status' => $status,
                ],
            ]);
        } catch (ClientException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('sales_orders_show', ['id' => $id]);
    }

    #[Route(path: '/juridical-locations-select', name: 'sales_order_juridical_location_select')]
    public function getJuridicalLocationSelect(Request $request)
    {
        $order = new ApiData(['sso' => ['@id' => $request->query->get('sso')]]);
        $form = $this->createForm(OrderType::class, $order, ['juridical_location_only' => true]);

        if (!$form->has('juridicalLocation')) {
            return new Response(null, Response::HTTP_OK);
        }

        return $this->render('sales/orders/partial/form/_juridical_location.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/baan-customer-number-select', name: 'sales_order_baan_customer_number_select')]
    public function getBaanCustomerNumberSelect(Request $request)
    {
        $order = new ApiData(['sso' => ['@id' => $request->query->get('sso')]]);
        $form = $this->createForm(OrderType::class, $order, ['baan_customer_number_only' => true]);

        if (!$form->has('baanCustomerNumber')) {
            return new Response(null, Response::HTTP_OK);
        }

        return $this->render('sales/orders/partial/form/_baan_customer_numbers.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/infor-ln-bp-code-select', name: 'sales_order_ln_bp_code_select')]
    public function getInforLnBpCodeSelect(Request $request): JsonResponse
    {
        $customerIri = $request->query->get('customerIri');
        $customer = $this->client->get($customerIri);

        return new JsonResponse(array_merge($customer['inforLnBusinessPartnerCodes'], ['N/A']));
    }
}
