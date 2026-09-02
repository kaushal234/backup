<?php

declare(strict_types=1);

namespace AppBundle\Controller\Parts;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Parts\SPQ\QuotationFiltersType;
use AppBundle\Filters\Type\Parts\SPQ\QuotationLineReportFiltersType;
use AppBundle\Filters\Type\Parts\SPQ\QuotationPartNumberType;
use AppBundle\Form\Type\IdSearchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/parts/spq', defaults: ['alvest_module' => 'SPQ', 'moduleDomain' => 'spq_quotations'])]
class SPQController extends AbstractController
{
    final public const QUOTATIONS_PER_PAGE = 100;
    private readonly Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    #[Route(path: '', name: 'spq_quotations_home', methods: 'GET|POST')]
    #[Template('parts/spq/dashboard.html.twig')]
    public function dashboard(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/quotations', 'filters' => ['itemsPerPage' => 10, 'order' => ['createdAt' => 'DESC'], 'normalization_groups' => ['quotation_totals']]])] HydraCollection $quotations)
    {
        $user = $this->client->get('me');
        $quickAccess = $this->getQuickAccessForm();
        $quickSearch = $this->getQuickSearchForm();

        return [
            'user' => $user,
            'quickAccess' => $quickAccess->createView(),
            'quickSearch' => $quickSearch->createView(),
            'quotationsBySphByStatusReport' => $this->client->get('reports/resource=/parts/quotations;x=sph.name;y=status'),
            'myQuotationsBySphByStatusReport' => $this->client->get('reports/resource=/parts/quotations;x=sph.name;y=status', [
                'query' => [
                    'options' => [
                        'quoter' => Iri::id($user),
                    ],
                ],
            ]),
            'latestQuotations' => $quotations,
        ];
    }

    #[Route(path: '/search', name: 'spq_quotations_search', methods: 'POST')]
    public function search(Request $request): RedirectResponse
    {
        $form = $this->getQuickAccessForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->get(\sprintf('parts/quotations/%s', $form->get('id')->getData()));

                return $this->redirectToRoute('spq_quotations_show', $form->getData());
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('SPQ #%s does not exist', $form->get('id')->getData()));
            }
        }

        if (null === $target = $request->headers->get('referer')) {
            $target = $this->generateUrl('spq_quotations_home');
        }

        return $this->redirect($target);
    }

    #[Route(path: '/quotations', name: 'spq_quotations_list', methods: 'GET|POST')]
    #[Template('parts/spq/list.html.twig')]
    public function quotationsList(Request $request, CsvStreamedResponseFactory $csvStreamedResponseFactory)
    {
        $user = $this->client->get('me');

        $page = $request->query->getInt('page', 1);

        $quickAccess = $this->getQuickAccessForm();
        $quickSearch = $this->getQuickSearchForm();

        $quickAccess->handleRequest($request);
        if ($quickAccess->isSubmitted() && $quickAccess->isValid()) {
            try {
                $this->client->get(\sprintf('parts/quotations/%s', $quickAccess->get('id')->getData()));

                return $this->redirectToRoute('spq_quotations_show', ['id' => $quickAccess->get('id')->getData()]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('SPQ #%s does not exist', $quickAccess->get('id')->getData()));
            }
        }

        $parameters = [];

        $formFilters = $this->container->get('form.factory')->createNamed(
            '',
            QuotationFiltersType::class,
            [],
            [
                'action' => $this->generateUrl('spq_quotations_list'),
                'method' => 'GET',
            ]
        );

        // Fix to allow to pass SPH name instead of Iri
        if ($request->query->has('sph')) {
            $sphChoices = $formFilters->get('sph')->getConfig()->getOption('choices');
            if (\array_key_exists($request->query->get('sph'), $sphChoices)) {
                $request->query->set('sph', $sphChoices[$request->query->get('sph')]);
            } elseif (!\in_array($request->query->get('sph'), $sphChoices, true)) {
                $request->query->remove('sph');
            }
        }

        $status = null;
        if ($request->query->has('status') && 'ALL' === $status = $request->query->get('status')) {
            $request->query->remove('status');
        }

        $posterChoices = $formFilters->get('poster')->getConfig()->getOption('choices');
        if ($request->query->has('poster') && !\in_array($request->query->get('poster'), $posterChoices, true)) {
            $request->query->remove('poster');
        }

        $quoterChoices = $formFilters->get('quoter')->getConfig()->getOption('choices');
        if ($request->query->has('quoter')) {
            $quoter = $request->query->get('quoter');
            if ('PENDING' === $status && !$request->query->has('poster') && \in_array($quoter, $posterChoices, true)) {
                // trying to override the quoter information as not defined in PENDING status
                // it allow matrix table to work properly
                $request->query->set('poster', $quoter);
                $request->query->remove('quoter');
            } elseif (!\in_array($quoter, $quoterChoices, true)) {
                $request->query->remove('quoter');
            }
        }

        if (1 !== $page) {
            $parameters['page'] = $page;
        }

        $parameters['itemsPerPage'] = self::QUOTATIONS_PER_PAGE;
        $parameters['order'] = ['createdAt' => 'DESC'];
        $parameters['normalization_groups'] = ['quotation_totals'];

        $formFilters->handleRequest($request);

        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = array_merge($parameters, $formFilters->getData());

            if ($formFilters->getClickedButton() && 'download' === $formFilters->getClickedButton()->getName()) {
                $parameters['itemsPerPage'] = 500;
                $parameters['properties'] = [
                    'id', 'createdAt', 'receivedAt', 'suspendedAt', 'expiredAt', 'closedAt', 'submittedAt', 'firstSubmittedAt',
                    'poster' => ['firstname', 'lastname'], 'source' => ['firstname', 'lastname'], 'quoter' => ['firstname', 'lastname'],
                    'sph' => ['name', 'erp'], 'rfq', 'baanCustomerNumber', 'baanCustomerName', 'baanSalesOrder', 'customerPurchaseOrder', 'status',
                    'requestType' => ['name'], 'customerName', 'contactEmails', 'currency', 'reason',
                ];

                return $csvStreamedResponseFactory->create('parts/quotations', $parameters);
            }
        } else {
            $parameters['sph'] = $formFilters->get('sph')->getData();
        }

        return [
            'user' => $user,
            'quickAccess' => $quickAccess->createView(),
            'quickSearch' => $quickSearch->createView(),
            'formFilters' => $formFilters->createView(),
            'quotations' => $this->client->findBy('parts/quotations', $parameters),
            'currentPage' => $page,
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '/quotations/{id}/show', name: 'spq_quotations_show', methods: 'GET')]
    #[Template('parts/spq/show.html.twig')]
    public function quotationShow(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/quotations', 'filters' => ['normalization_groups' => ['location_detail']]])] ApiData $quotation)
    {
        $erp = $quotation['sph']['erp'];
        $quickAccess = $this->getQuickAccessForm();
        $quickSearch = $this->getQuickSearchForm();

        $countries = [];
        $forwardingAgent = $paymentTerm = $order = $customer = null;

        return [
            'quickAccess' => $quickAccess->createView(),
            'quickSearch' => $quickSearch->createView(),
            'quotation' => $quotation,
            'countries' => $countries,
            'forwardingAgent' => $forwardingAgent,
            'paymentTerm' => $paymentTerm,
            'order' => $order,
            'customer' => $customer,
        ];
    }

    #[Route(path: '/quotations/{id}/files', name: 'spq_quotation_files', methods: 'GET')]
    #[Template('parts/spq/files.html.twig')]
    public function quotationFiles(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/quotations'])] ApiData $quotation)
    {
        $quickAccess = $this->getQuickAccessForm();
        $quickSearch = $this->getQuickSearchForm();

        return [
            'quotation' => $quotation,
            'quickAccess' => $quickAccess->createView(),
            'quickSearch' => $quickSearch->createView(),
        ];
    }

    #[Route(path: '/quotations/{quotationId}/{fileType}/{id}', requirements: ['fileType' => 'quotation_files|attached_files', 'quotationId' => '\d+', 'id' => '\d+'], name: 'spq_quotation_files_show', methods: 'GET')]
    public function showFile($quotationId, $fileType, $id, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        return $fileStreamedResponseFactory->create(\sprintf('parts/quotations/%s/%s/%s', $quotationId, $fileType, $id));
    }

    #[Route(path: '/quotations/{id}/logs', name: 'spq_quotation_logs', methods: 'GET')]
    #[Template('parts/spq/logs.html.twig')]
    public function quotationLogs(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/quotations'])] ApiData $quotation)
    {
        $quickAccess = $this->getQuickAccessForm();
        $quickSearch = $this->getQuickSearchForm();

        return [
            'quotation' => $quotation,
            'quickAccess' => $quickAccess->createView(),
            'quickSearch' => $quickSearch->createView(),
        ];
    }

    #[Route(path: '/quotations/{id}/tasks', name: 'spq_quotation_tasks', methods: 'GET')]
    #[Template('parts/spq/tasks.html.twig')]
    public function quotationTasks(#[ApiValueResolverAttribute(parameters: ['resource' => 'parts/quotations'])] ApiData $quotation)
    {
        $quickAccess = $this->getQuickAccessForm();
        $quickSearch = $this->getQuickSearchForm();

        return [
            'quotation' => $quotation,
            'quickAccess' => $quickAccess->createView(),
            'quickSearch' => $quickSearch->createView(),
        ];
    }

    #[Route(path: '/reports/{type}', requirements: ['type' => 'quotationLines'], defaults: ['type' => 'quotationLines'], name: 'spq_reports', methods: 'GET')]
    #[Template('parts/spq/reports.html.twig')]
    public function reports($type, Request $request, CsvStreamedResponseFactory $csvStreamedResponseFactory)
    {
        $quickAccess = $this->getQuickAccessForm();
        $quickSearch = $this->getQuickSearchForm();
        $formFilters = $this->container->get('form.factory')->createNamed(
            '',
            QuotationLineReportFiltersType::class,
            [],
            [
                'action' => $this->generateUrl('spq_reports', ['type' => $type]),
                'method' => 'GET',
            ]
        );
        $quotationLines = [];

        $formFilters->handleRequest($request);

        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $parameters = [
                'itemsPerPage' => self::QUOTATIONS_PER_PAGE,
                'normalization_groups' => ['quotation_lines:reports', 'people_list', 'location_public'],
                'properties' => [
                    'id', 'status', 'partNumber', 'description', 'unitBasePrice', 'quantity', 'salesUnit', 'currency', 'discount', 'totalPrice', 'commission', 'createdAt',
                    'quotation' => ['id', 'status', 'baanCustomerNumber', 'sph' => ['name', 'erp'], 'customerPurchaseOrder', 'baanSalesOrder', 'baanCustomerName', 'poster' => ['firstname', 'lastname'], 'reason', 'payableService'],
                ],
            ];
            $parameters = array_merge($parameters, $formFilters->getData());
            if ($formFilters->getClickedButton() && 'download' === $formFilters->getClickedButton()->getName()) {
                return $csvStreamedResponseFactory->create('parts/quotation_lines', $parameters);
            }

            $quotationLines = $this->client->search('parts/quotation_lines', ['query' => $parameters]);
        }

        return [
            'quotationLines' => $quotationLines,
            'formFilters' => $formFilters->createView(),
            'type' => $type,
            'quickAccess' => $quickAccess->createView(),
            'quickSearch' => $quickSearch->createView(),
        ];
    }

    /**
     * @return FormInterface
     */
    private function getQuickAccessForm()
    {
        return $this->createForm(IdSearchType::class, null, ['id_label' => 'spq.form.by_id', 'id_placeholder' => 'spq.form.by_id_placeholder', 'translation_domain' => 'spq', 'action' => $this->generateUrl('spq_quotations_search')]);
    }

    /**
     * @return FormInterface
     */
    private function getQuickSearchForm()
    {
        return $this->container->get('form.factory')->createNamed('', QuotationPartNumberType::class, null, ['action' => $this->generateUrl('spq_quotations_list'), 'method' => 'GET']);
    }
}
