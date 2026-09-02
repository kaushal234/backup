<?php

declare(strict_types=1);

namespace AppBundle\Controller\Finance;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Finance\InvoiceRecordFilterType;
use AppBundle\Form\Type\Finance\InvoiceRecord\InvoiceRecordType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/finance/invoice-records', defaults: ['alvest_module' => 'AR', 'breadcrumb_label' => 'menu.invoice_record.title', 'moduleDomain' => 'invoice_record'])]
class InvoiceRecordController extends AbstractController
{
    final public const RESOURCE_URL = 'finance/invoice_records';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class]);
    }

    #[Route(path: '', name: 'invoice_record_home', methods: ['GET'])]
    #[Template('finance/invoice-record/home.html.twig')]
    public function home(Request $request)
    {
        $parameters = ['itemsPerPage' => 10];
        $reportTitle = 'account_receivable.invoice_record.last_invoices';
        $searchTable = false;
        $formFilter = $this->container->get('form.factory')->createNamed('filter', InvoiceRecordFilterType::class, [], []);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = [
                'itemsPerPage' => 2000,
                'pagination' => false,
            ];
            $parameters = array_merge($parameters, $formFilter->getData());

            $reportTitle = 'account_receivable.invoice_record.filtered';
            $searchTable = true;
        }

        return [
            'invoiceRecords' => $this->container->get(Client::class)->findBy(self::RESOURCE_URL, $parameters, ['id' => 'desc']),
            'reportTitle' => $reportTitle,
            'searchTable' => $searchTable,
            'formFilter' => $formFilter->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'invoice_record_show', methods: ['GET'])]
    #[Template('finance/invoice-record/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $invoiceRecord)
    {
        return ['invoiceRecord' => $invoiceRecord];
    }

    #[Route(path: '/{id}/edit', name: 'invoice_record_edit', methods: ['GET|POST'])]
    #[Template('finance/invoice-record/write.html.twig')]
    #[IsGranted(attribute: 'INVOICE_RECORD_WRITE_VOTER', subject: new Expression('args["invoiceRecord"].getIri()'))]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $invoiceRecord)
    {
        $form = $this->container->get('form.factory')->createNamed('form', InvoiceRecordType::class, $invoiceRecord);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = [...$form->getData(), 'comment' => $form->get('comment')->getData()];
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('account_receivable.invoice_record.edit.success', [], AccountReceivableController::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('invoice_record_show', ['id' => Iri::id($invoiceRecord)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'object' => $invoiceRecord,
            'action' => 'invoice_record_edit',
            'title' => 'account_receivable.invoice_record.edit.title',
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/add', name: 'invoice_record_add', methods: ['GET|POST'])]
    #[Template('finance/invoice-record/write.html.twig')]
    public function add(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'finance/account_receivables'])] ApiData $accountReceivable)
    {
        $form = $this->container->get('form.factory')->createNamed('form', InvoiceRecordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $payload = array_merge($form->getData(), [
                    'customerErpReference' => $accountReceivable['customerErpReference']['@id'],
                    'currency' => $accountReceivable['currency']['@id'],
                    'originalAmount' => $accountReceivable['originalAmount'],
                    'invoiceNumber' => $accountReceivable['erpInvoiceNumber'],
                ]);

                $this->container->get(Client::class)->save(self::RESOURCE_URL, $payload);
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('account_receivable.invoice_record.add.success', [], AccountReceivableController::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('account_receivable_show', ['id' => Iri::id($accountReceivable)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'object' => $accountReceivable,
            'action' => 'invoice_record_add',
            'title' => 'account_receivable.invoice_record.add.title',
            'form' => $form->createView(),
        ];
    }
}
