<?php

declare(strict_types=1);

namespace AppBundle\Controller\Finance;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Finance\AccountReceivableFilterType;
use AppBundle\Filters\Type\Finance\AccountReceivableMyTasksFilterType;
use AppBundle\Filters\Type\Finance\AccountReceivableOverviewFilterType;
use AppBundle\Form\Type\Finance\AccountReceivable\AccountReceivableUploadType;
use AppBundle\Form\Type\Finance\InvoiceRecord\InvoiceRecordBatchType;
use AppBundle\Form\Type\Finance\InvoiceRecord\InvoiceRecordRevisedEndDateType;
use AppBundle\Form\Type\Finance\InvoiceRecord\InvoiceRecordType;
use Cake\Chronos\Chronos;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/finance/account-receivables', defaults: ['alvest_module' => 'AR', 'moduleDomain' => 'account_receivable'])]
class AccountReceivableController extends AbstractController
{
    final public const TRANSLATION_DOMAIN = 'account_receivable';
    private const RESOURCE_URL = 'finance/account_receivables';
    private const RESOURCE_OVERVIEW_URL = 'finance/account_receivable_overviews';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, FileStreamedResponseFactory::class]);
    }

    #[Route(path: '/search', name: 'account_receivable_search', methods: ['GET|POST'], defaults: ['label' => 'account_receivable.search'])]
    #[Template('finance/account-receivable/search.html.twig')]
    public function search(Request $request)
    {
        $parameters = [
            'itemsPerPage' => 10,
            'normalizationGroups' => ['invoice_record'],
        ];

        $me = $this->container->get(Client::class)->get('/me');
        $ssdFilter = $this->isGranted('ACL_ROLE_EVP');

        $reportTitle = 'account_receivable.last_ar';
        $searchTable = false;
        $formFilter = $this->container->get('form.factory')->createNamed('', AccountReceivableFilterType::class, [], ['asmOfSSD' => $ssdFilter, 'userIri' => $me['@id']]);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = [
                'itemsPerPage' => 2000,
                'pagination' => false,
            ];

            $parameters = array_merge($parameters, $formFilter->getData());
            if (null !== $formFilter->getClickedButton() && 'download' === $formFilter->getClickedButton()->getName()) {
                $parameters['normalizationGroups'] = ['invoice_record'];

                return $this->container->get(FileStreamedResponseFactory::class)->create(self::RESOURCE_URL, ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']], 'account_receivables.xlsx');
            }

            $reportTitle = 'account_receivable.filtered';
            $searchTable = true;
        }

        $client = $this->container->get(Client::class);
        $accountReceivables = $client->findBy(self::RESOURCE_URL, $parameters, ['id' => 'desc']);
        $data = [];
        foreach ($accountReceivables as $accountReceivable) {
            $accountReceivableId = Iri::id($accountReceivable);
            $data[$accountReceivableId] = [
                'accountReceivableId' => $accountReceivableId,
                'accountReceivable' => $accountReceivable,
                'category' => $accountReceivable['invoiceRecord']['category'] ?? null,
                'expectedPaymentDate' => $accountReceivable['invoiceRecord']['expectedPaymentDate'] ?? null,
            ];
        }

        $formComments = $this->createForm(InvoiceRecordBatchType::class, ['accountReceivables' => $data]);
        $formComments->handleRequest($request);

        if ($formComments->isSubmitted()) {
            $comment = $formComments->get('comment')->getData();
            $success = false;
            foreach ($formComments->get('accountReceivables')->getData() as $formData) {
                $accountReceivableId = $formData['accountReceivableId'];
                if ($formData['active']) {
                    $payload = [
                        'category' => $formData['category'],
                        'expectedPaymentDate' => $formData['expectedPaymentDate'],
                        'comment' => $comment,
                    ];

                    $indexedAccountReceivable = $data[$accountReceivableId]['accountReceivable'];
                    if (null === $indexedAccountReceivable['invoiceRecord']) {
                        $payload += [
                            'originalAmount' => $indexedAccountReceivable['originalAmount'],
                            'currency' => $indexedAccountReceivable['currency']['@id'],
                            'customerErpReference' => $indexedAccountReceivable['customerErpReference']['@id'],
                            'invoiceNumber' => $indexedAccountReceivable['erpInvoiceNumber'],
                        ];
                    } else {
                        $payload += ['@id' => \sprintf('/%s/%s', InvoiceRecordController::RESOURCE_URL, $indexedAccountReceivable['invoiceRecord']['id'])];
                    }

                    try {
                        $client->save(InvoiceRecordController::RESOURCE_URL, $payload);
                        $success = true;
                    } catch (ClientException $e) {
                        $errors = json_decode($e->getResponse()->getContent(false), true);
                        foreach ($errors['violations'] ?? [] as $error) {
                            $this->addFlash(
                                'warning',
                                \sprintf('AR #%s: %s', $indexedAccountReceivable['id'], $error['message'])
                            );
                        }
                    }
                }
            }

            if ($success) {
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('account_receivable.message.quick_edit', [], 'account_receivable')
                );
            }

            return $this->redirectToRoute('account_receivable_search');
        }

        return [
            'accountReceivables' => $accountReceivables,
            'reportTitle' => $reportTitle,
            'searchTable' => $searchTable,
            'formFilter' => $formFilter->createView(),
            'formComment' => $formComments->createView(),
        ];
    }

    #[Route(path: '/dashboard', name: 'account_receivable_home', methods: ['GET'])]
    #[Template('finance/account-receivable/dashboard.html.twig')]
    public function dashboard(Request $request)
    {
        $parameters = [];
        $options = [];
        $client = $this->container->get(Client::class);
        $me = $client->get('/me');

        $asmFilter = $this->isGranted('ACL_ROLE_ASM');
        $ssdFilter = $this->isGranted('ACL_ROLE_EVP');

        if ($asmFilter) {
            $options['asms'] = [$me['@id']];
        }

        if ($ssdFilter) {
            $options['ssds'] = [$me['@id']];
        }

        $formFilter = $this->container->get('form.factory')->createNamed('', AccountReceivableOverviewFilterType::class, [], ['asmOfSSD' => $ssdFilter, 'me' => $me]);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = $formFilter->getData();

            foreach (['asms' => 'customerErpReference.customer.mainSalesRepresentative.asm', 'ssds' => 'customerErpReference.customer.mainSalesRepresentative.asm.supervisor'] as $key => $value) {
                if (isset($parameters[$value])) {
                    $options[$key] = $parameters[$value];
                }
            }

            if (null !== $formFilter->getClickedButton() && 'download' === $formFilter->getClickedButton()->getName()) {
                return $this->container->get(FileStreamedResponseFactory::class)->create(self::RESOURCE_OVERVIEW_URL, ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']], 'account_receivable_overview.xlsx');
            }
        }

        $parameters += [
            'currency' => $me['businessUnit']['location']['currency']['@id'],
            'customerErpReference.sso' => [$me['businessUnit']['location']['@id']],
        ];

        if ($asmFilter) {
            $parameters += [$ssdFilter ? 'customerErpReference.customer.mainSalesRepresentative.asm.supervisor' : 'customerErpReference.customer.mainSalesRepresentative.asm' => [$me['@id']]];
        }

        $overviewTotalAR = $client->findBy(self::RESOURCE_OVERVIEW_URL, $parameters, []);

        $total = [
            'totalValue' => 0,
            'totalPastDue' => 0,
            'notPastDue' => 0,
            'pastDueOneMonth' => 0,
            'pastDueTwoMonths' => 0,
            'pastDueThreeMonths' => 0,
            'pastDueSixMonths' => 0,
            'pastDueMoreThanSixMonths' => 0,
        ];

        $pastDue = 0;
        $pastDueTwoMonths = 0;
        foreach ($overviewTotalAR as $item) {
            foreach ($item as $key => $value) {
                if (\in_array($key, ['totalValue', 'notPastDue', 'pastDueOneMonth', 'pastDueTwoMonths', 'pastDueThreeMonths', 'pastDueSixMonths', 'pastDueMoreThanSixMonths'], true)) {
                    $total[$key] += $value;
                }

                if (\in_array($key, ['pastDueOneMonth', 'pastDueTwoMonths', 'pastDueThreeMonths', 'pastDueSixMonths', 'pastDueMoreThanSixMonths'], true)) {
                    $pastDue += $value;
                }

                if (\in_array($key, ['pastDueThreeMonths', 'pastDueSixMonths', 'pastDueMoreThanSixMonths'], true)) {
                    $pastDueTwoMonths += $value;
                }
            }
        }

        $total['totalPastDue'] = 0 !== $total['totalValue'] ? $total['totalValue'] - $total['notPastDue'] : 0;
        $total['pastDuePercentage'] = 0 !== $total['totalValue'] ? $pastDue * 100 / $total['totalValue'] : 0;
        $total['pastDuePercentageTwoMonths'] = 0 !== $total['totalValue'] ? $pastDueTwoMonths * 100 / $total['totalValue'] : 0;
        $currency = $client->find('finance/currencies', Iri::id($parameters['currency']));
        unset($parameters['currency']);

        return [
            'total' => $total,
            'currency' => $currency,
            'creditLimitCustomers' => $this->container->get(Client::class)->get('reports/resource=/finance/account_receivables;x=credit_limit;y=value', [
                'query' => [
                    'options' => $options + ['currency' => $currency['@id']],
                ],
            ]),
            'formFilter' => $formFilter->createView(),
            'accountReceivableOverview' => $overviewTotalAR,
            'overviewPastDueNinetyDays' => $client->findBy(self::RESOURCE_URL, $parameters + ['context' => ['convertTo' => $currency['@id']], 'dueDate' => ['before' => Chronos::parse('-91 days')->toDateTimeString()]], ['balanceAmount' => 'DESC']),
            'overviewThirtyDaysGreaterThan' => $client->findBy(self::RESOURCE_URL, $parameters + ['context' => ['convertTo' => $currency['@id']], 'balanceAmount' => ['gt' => 50000], 'dueDate' => ['before' => Chronos::parse('-30 days')->toDateTimeString(), 'after' => Chronos::parse('-45 days')->toDateTimeString()]], ['dueDate' => 'ASC']),
            'summaryNextComingGreaterThan' => $client->findBy(self::RESOURCE_URL, $parameters + ['context' => ['convertTo' => $currency['@id']], 'balanceAmount' => ['gt' => 50000], 'dueDate' => ['after' => Chronos::now()->toDateTimeString()]], ['dueDate' => 'ASC']),
        ];
    }

    #[Route(path: '/my-tasks', name: 'account_receivable_my_tasks', methods: ['GET'], defaults: ['label' => 'account_receivable.fields.my_tasks'])]
    #[Template('finance/account-receivable/my-tasks.html.twig')]
    public function myTasks(Request $request)
    {
        $client = $this->container->get(Client::class);
        $user = $client->get('/me');
        $parameters = [];
        $formFilter = null;
        $ssd = $this->isGranted('ACL_ROLE_EVP') ? $user : null;

        if (null !== $ssd || $this->isGranted('ACL_ROLE_CFO') || $this->isGranted('ACL_SUPERUSER') || $this->isGranted('MOO_AR')) {
            $formFilter = $this->container->get('form.factory')->createNamed('filter', AccountReceivableMyTasksFilterType::class, [], ['asmOfSSD' => $ssd, 'userIri' => $user['@id']]);
        }

        $users = [];
        $userIris = [];
        if (null !== $formFilter) {
            $formFilter->handleRequest($request);
            if ($formFilter->isSubmitted() && $formFilter->isValid()) {
                $data = $formFilter->getData();
                $parameters = $data;

                if (isset($data['customerErpReference.customer.mainSalesRepresentative.asm'])) {
                    $userIris = $data['customerErpReference.customer.mainSalesRepresentative.asm'];
                }

                if (isset($data['customerErpReference.customer.mainSalesRepresentative.asm.supervisor'])) {
                    $userIris = [$ssdIri = $data['customerErpReference.customer.mainSalesRepresentative.asm.supervisor']];
                    $ssd = $client->find('people', Iri::id($ssdIri));
                }

                foreach ($userIris as $iri) {
                    $users[] = $client->find('people', Iri::id($iri));
                }
            }
        }

        $parameters += [
            null !== $ssd ? 'customerErpReference.customer.mainSalesRepresentative.asm.supervisor' : 'customerErpReference.customer.mainSalesRepresentative.asm' => array_reduce([] === $users ? [$user] : $users, static function ($memo, $user) {
                $memo[] = $user['@id'];

                return $memo;
            }, []),
            'delinquent' => true,
            'normalizationGroups' => ['invoice_record'],
        ];

        $overview = $client->findBy(self::RESOURCE_URL, $parameters, ['balanceAmount' => 'DESC']);

        return [
            'formFilter' => null !== $formFilter ? $formFilter->createView() : $formFilter,
            'users' => [] === $users ? [$user] : $users,
            'overview' => $overview,
            'ssd' => $ssd,
        ];
    }

    #[Route(path: '/upload', name: 'account_receivable_upload', methods: ['GET|POST|PUT'], defaults: ['label' => 'account_receivable.upload'])]
    #[Template('finance/account-receivable/upload.html.twig')]
    #[IsGranted('FEATURE_ACCOUNT_RECEIVABLES_WRITE')]
    public function upload(Request $request)
    {
        $formFileUpload = $this->container->get('form.factory')->createNamed('upload_file', AccountReceivableUploadType::class, [], ['upload_file' => true]);

        $formFileUpload->handleRequest($request);
        if ($formFileUpload->isSubmitted() && $formFileUpload->isValid()) {
            if (($file = $formFileUpload->get('file')->getData()) instanceof UploadedFile) {
                try {
                    $multiPart['file'] = DataPart::fromPath($file->getPathname(), $file->getClientOriginalName());
                    $formData = new FormDataPart($multiPart);

                    $this->container->get(Client::class)->post(\sprintf('finance/account_receivables/import_file/%s', Iri::id($formFileUpload->get('sso')->getData())), [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);
                } catch (ClientException $e) {
                    $errorDescription = $e->getResponse()->toArray(false);
                    $this->addFlash('error', $errorDescription['hydra:description']);

                    return $this->redirectToRoute('account_receivable_upload');
                }
            }

            return $this->redirectToRoute('account_receivable_home');
        }

        return [
            'formFileUpload' => $formFileUpload->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'account_receivable_show', methods: ['GET|POST|PUT'])]
    #[Template('finance/account-receivable/show.html.twig')]
    #[IsGranted(attribute: 'ACCOUNT_RECEIVABLES_VIEW_VOTER', subject: new Expression('args["accountReceivable"].getIri()'))]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'finance/account_receivables', 'filters' => ['normalizationGroups' => ['invoice_record']]])] ApiData $accountReceivable, Request $request)
    {
        $dataForInvoice = [
            'originalAmount' => $accountReceivable['originalAmount'],
            'currency' => $accountReceivable['currency']['@id'],
            'customerErpReference' => $accountReceivable['customerErpReference']['@id'],
            'invoiceNumber' => $accountReceivable['erpInvoiceNumber'],
        ];

        $invoiceIri = null;
        if (null !== ($invoiceRecord = $accountReceivable['invoiceRecord'])) {
            $invoiceIri = \sprintf('/%s/%d', InvoiceRecordController::RESOURCE_URL, $invoiceRecord['id']);
            $invoiceRecord['comments'] = $this->container->get(Client::class)->findBy('comments', ['resource' => $invoiceIri]);
            $dataForInvoice['@id'] = $invoiceIri;
        }

        $interval = (new \DateTime())->diff(new \DateTime($accountReceivable['dueDate']))->format('%r%a');
        $revisedInterval = null !== $accountReceivable['invoiceRecord'] && null !== $accountReceivable['invoiceRecord']['revisedDueDate'] ? (new \DateTime())->diff(new \DateTime($accountReceivable['invoiceRecord']['revisedDueDate']))->format('%r%a') : 0;

        $method = null === $invoiceRecord ? Request::METHOD_POST : Request::METHOD_PUT;
        $form = $this->container->get('form.factory')->createNamed('form', InvoiceRecordType::class, $invoiceRecord ?? [], ['method' => $method]);
        $revisedDueDateForm = $this->container->get('form.factory')->createNamed('formRevisedDueDate', InvoiceRecordRevisedEndDateType::class, $invoiceRecord ?? [], ['method' => $method]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->container->get(Client::class)->save(InvoiceRecordController::RESOURCE_URL, array_merge($dataForInvoice, $data));
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('account_receivable.invoice_record.add.success', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('account_receivable_show', ['id' => Iri::id($accountReceivable)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        $revisedDueDateForm->handleRequest($request);
        if ($revisedDueDateForm->isSubmitted() && $revisedDueDateForm->isValid()) {
            try {
                $data = $revisedDueDateForm->getData();
                $this->container->get(Client::class)->save(InvoiceRecordController::RESOURCE_URL, array_merge($dataForInvoice, $data));
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('account_receivable.invoice_record.add.success', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('account_receivable_show', ['id' => Iri::id($accountReceivable)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $revisedDueDateForm);
            }
        }
        $revisedDueDateEditable = null === ($invoiceRecord['revisedDueDate'] ?? null) && $this->isGranted('INVOICE_RECORD_DUE_DATE_VOTER', $accountReceivable['customerErpReference']['sso']['@id']);

        return [
            'accountReceivable' => $accountReceivable,
            'invoiceRecord' => $invoiceRecord,
            'invoiceIri' => $invoiceIri,
            'interval' => (int) $interval,
            'revisedInterval' => (int) $revisedInterval,
            'form' => $form->createView(),
            'revisedDueDateForm' => $revisedDueDateForm->createView(),
            'revisedDueDateEditable' => $revisedDueDateEditable,
        ];
    }
}
