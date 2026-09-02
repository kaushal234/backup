<?php

declare(strict_types=1);

namespace AppBundle\Controller\Support;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Http\ZipStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Support\ManualDataTableType;
use AppBundle\Filters\Type\Support\ManualFilterType;
use AppBundle\Filters\Type\Support\ManualPartFilterType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\LegacyIdSearchType;
use AppBundle\Form\Type\Support\ManualDocumentCollectionType;
use AppBundle\Form\Type\Support\ManualType;
use AppBundle\Twig\Extension\QRCodeGeneratorExtension;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/support/manuals', defaults: ['alvest_module' => 'PUBS', 'moduleDomain' => 'manuals'])]
class ManualController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    /** @var string */
    final public const RESOURCE_URL = 'support/manuals';

    final public const EXTRANET_MANUAL_URL = 'https://www.tld-gse.com/extranet/index.php?m[0]=manuals&m[1]=view&id=';

    private readonly Client $client;

    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    private readonly TranslatorInterface $translator;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly ZipStreamedResponseFactory $zipStreamedResponseFactory;

    private readonly QRCodeGeneratorExtension $qrCodeGeneratorExtension;

    public function __construct(Client $client, FormFactoryInterface $formFactory, FileStreamedResponseFactory $fileStreamedResponseFactory, TranslatorInterface $translator, ViolationMapper $violationMapper, ZipStreamedResponseFactory $zipStreamedResponseFactory, QRCodeGeneratorExtension $qrCodeGeneratorExtension)
    {
        $this->client = $client;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->zipStreamedResponseFactory = $zipStreamedResponseFactory;
        $this->qrCodeGeneratorExtension = $qrCodeGeneratorExtension;
    }

    #[Route(path: '', name: 'manuals_home', methods: ['GET|POST'], defaults: ['page' => 1])]
    #[Template('support/manuals/home.html.twig')]
    public function index(Request $request)
    {
        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By Id',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $this->client->get(\sprintf('support/manuals/%s', $id));

                return $this->redirectToRoute('manuals_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('Manual #%s does not exist', $id));
            }
        }

        $legacyIdSearchForm = $this->createForm(LegacyIdSearchType::class, null, [
            'legacy_id_label' => false,
            'legacy_id_placeholder' => 'By LegacyId',
        ]);

        $legacyIdSearchForm->handleRequest($request);
        if ($legacyIdSearchForm->isSubmitted() && $legacyIdSearchForm->isValid()) {
            $legacyId = $legacyIdSearchForm->get('legacyId')->getData();
            try {
                $manual = $this->client->findOneBy(self::RESOURCE_URL, ['legacyId' => $legacyId]);

                return $this->redirectToRoute('manuals_show', ['id' => $manual->getIriId()]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('Manual #%s does not exist', $legacyId));
            }
        }

        $datatable = $this->createDataTable(ManualDataTableType::class, ManualDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'idSearchForm' => $idSearchForm->createView(),
            'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
            'datatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'manuals_show', methods: ['GET'])]
    #[Template('support/manual/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'support/manuals'])] ApiData $manual, Request $request)
    {
        $documentsData = $documents = [];
        $categoriesOrder =
        [
            'Chapter 0',
            'Chapter 1',
            'Chapter 2',
            'Chapter 3',
            'Chapter 5',
            'Accessories and Options',
            'Body-Chassis',
            'Boom',
            'Bridge',
            'Covers and Panels',
            'Electrical System',
            'Elevator',
            'Hydraulic System',
            'Lifting-Scissors System',
            'Power Plant',
            'Suspension, Tires and Brakes',
            'User Interfaces and Cab',
            'PLUMBING',
            'Pneumatic System',
            'Refrigeration System',
            'TRANSMISSION',
        ];
        foreach ($categoriesOrder as $category) {
            $documentsData[$category] = [];
        }

        $formFilter = $this->formFactory->createNamed('', ManualFilterType::class, [], [
            'action' => $this->generateUrl('manuals_show', ['id' => $manual->getIriId()]),
            'method' => Request::METHOD_GET,
        ]);
        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $documents = $this->client->findBy(ManualDocumentController::RESOURCE_URL, [
                'parts.partNumber' => $formFilter->get('partNumber')->getData(),
                'manual' => $manual->getIri(),
                'normalizationGroups' => ['manual_document', 'manual_document_category'],
                'q' => $formFilter->get('partDescription')->getData(),
            ]);
        }

        if (!$formFilter->isSubmitted()) {
            $documents = $manual['documents'];
        }

        foreach ($documents as $document) {
            $document['erp'] = $manual['equipmentRecord']['manufacturerLocation']['erp'] ?? 0;
            $date = new \DateTime($manual['createdAt'] ?? 'now');
            $document['date'] = $date->format('Y-m-d');
            if (isset($document['category']['name']) && isset($documentsData[$document['category']['name']])) {
                $documentsData[$document['category']['name']][] = $document;
            }
        }

        return [
            'manual' => $manual,
            'documentsData' => array_filter($documentsData),
            'formFilter' => $formFilter->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'manuals_edit', methods: ['GET', 'POST'])]
    #[Template('support/manual/edit.html.twig')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'support/manuals'])] ApiData $manual)
    {
        $this->denyAccessUnlessGranted('MANUAL_EDIT_VOTER', $manual['equipmentRecord']['@id']);

        $form = $this->createForm(ManualType::class, $manual);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->translator->trans('support.manual.messages.success.edit', [], 'support')
                );

                return $this->redirectToRoute('manuals_show', ['id' => $manual->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'manual' => $manual,
            'form' => $form->createView(),
            'canEdit' => $this->isGranted('MANUAL_EDIT_VOTER', $manual['equipmentRecord']['@id']),
        ];
    }

    #[Route(path: '/{id}/edit-documents-by-category/{category}', name: 'manuals_edit_documents_by_category', methods: ['GET', 'POST'])]
    #[Template('support/manual/editDocumentsByCategory.html.twig')]
    public function editDocumentsByCategory(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'support/manuals'])] ApiData $manual, $category): RedirectResponse|array
    {
        $documents = $manual['documents'];
        $filteredDocuments = array_filter($documents, static function ($document) use ($category) {
            return isset($document['category']['name']) && $document['category']['name'] === $category;
        });
        $form = $this->createForm(ManualDocumentCollectionType::class, ['documents' => $filteredDocuments]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $otherDocuments = array_filter($documents, static function ($document) use ($category) {
                    return !isset($document['category']) || $document['category']['name'] !== $category;
                });
                // need only @id property on other documents
                $otherDocuments = array_map(static function ($document) {
                    return ['@id' => $document['@id']];
                }, $otherDocuments);

                // regroup documents
                $newDocumentCollection = array_merge($otherDocuments, $form->getData()['documents']);

                $data = [
                    '@id' => $manual['@id'],
                    'documents' => $newDocumentCollection,
                ];

                $this->client->save(self::RESOURCE_URL, $data);
                $this->addFlash(
                    'success',
                    $this->translator->trans('support.manual.messages.success.edit', [], 'support')
                );

                return $this->redirectToRoute('manuals_show', ['id' => $manual->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'category' => $category,
            'manual' => $manual,
            'canEdit' => $this->isGranted('MANUAL_EDIT_VOTER', $manual['equipmentRecord']['@id']),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'manuals_delete', methods: ['GET|DELETE'])]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => 'support/manuals'])] ApiData $manual): RedirectResponse
    {
        $this->denyAccessUnlessGranted('MANUAL_EDIT_VOTER', $manual['equipmentRecord']['@id']);

        try {
            $this->client->remove(self::RESOURCE_URL, $manual['id']);

            $this->addFlash(
                'success',
                $this->translator->trans('support.manual.messages.success.delete', [], 'support')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('support.manual.messages.error.delete', [], 'support')
            );
        }

        return $this->redirectToRoute('manuals_home');
    }

    #[Route(path: '/{id}/rspl', name: 'manuals_rspl', methods: ['GET'])]
    #[Template('support/manual/rspl.html.twig')]
    public function showRSPL(#[ApiValueResolverAttribute(parameters: ['resource' => 'support/manuals'])] ApiData $manual, Request $request)
    {
        $formFilters = $this->createForm(ManualPartFilterType::class, null, [
            'method' => 'GET',
        ]);
        $formFilters->handleRequest($request);

        $parameters = [
            'document.manual' => $manual->getIriId(),
            'q' => 1,
        ];

        if ($formFilters->isSubmitted() && $formFilters->isValid()) {
            $groups = $formFilters->getData()['group'];
            if (\in_array('P', $groups, true)) {
                $parameters = array_merge($parameters, ['preventive' => 1]);
            }
            if (\in_array('M', $groups, true)) {
                $parameters = array_merge($parameters, ['maintenance' => 1]);
            }
            if (\in_array('O', $groups, true)) {
                $parameters = array_merge($parameters, ['overhaul' => 1]);
            }
            if (\in_array('C', $groups, true)) {
                $parameters = array_merge($parameters, ['critical' => 1]);
            }
        }

        $parts = $this->client->search('support/manual_parts', ['query' => $parameters]);

        return [
            'formFilters' => $formFilters->createView(),
            'manual' => $manual,
            'parts' => $parts,
            'canEdit' => $this->isGranted('MANUAL_EDIT_VOTER', $manual['equipmentRecord']['@id']),
        ];
    }

    /**
     * @return StreamedResponse
     */
    #[Route(path: '/{manualId}/chapter4', name: 'manual_download_chapter4_pdf_file', methods: 'GET', requirements: ['manualId' => '\d+'])]
    public function showChapter4Pdf(int $manualId)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf(self::RESOURCE_URL.'/%d/pdf/chapter4', $manualId), ['headers' => ['Accept' => 'application/pdf']]);
    }

    /**
     * @return StreamedResponse
     */
    #[Route(path: '/{manualId}/parts_list', name: 'manual_download_parts_list_pdf_file', methods: 'GET', requirements: ['manualId' => '\d+'])]
    public function showPartNumberListPdf(int $manualId)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf(self::RESOURCE_URL.'/%d/pdf/parts_list', $manualId), ['headers' => ['Accept' => 'application/pdf']]);
    }

    #[Route(path: '/{manualId}/zip', name: 'manual_download_zip', methods: 'GET', requirements: ['manualId' => '\d+'])]
    public function showAllFiles(int $manualId): StreamedResponse
    {
        return $this->zipStreamedResponseFactory->create(\sprintf(self::RESOURCE_URL.'/%d/zip', $manualId), \sprintf('manual_%d', $manualId));
    }

    #[Route(path: '/{id}/qrCode', name: 'manual_extranet_qrcode', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('support/manual/qr_code.html.twig')]
    public function showExtranetQRcode(#[ApiValueResolverAttribute(parameters: ['resource' => 'support/manuals'])] ApiData $manual)
    {
        $toEncode = \sprintf('%s%s', self::EXTRANET_MANUAL_URL, $manual['legacyId']);
        $qrCodeData = $this->qrCodeGeneratorExtension->generateQRCode($toEncode);

        return ['qrCodeData' => $qrCodeData, 'manual' => $manual];
    }
}
