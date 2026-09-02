<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Sales\CompetitorDataTableType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales', defaults: ['alvest_module' => 'COR', 'moduleDomain' => 'sales_competitors'])]
class CompetitorController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    public const COMPETITORS_URL = 'sales/competitors';

    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    private readonly FileManager $fileManager;

    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, FileManager $fileManager, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->fileManager = $fileManager;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
    }

    #[Route(path: '/competitors', name: 'sales_competitors_home', methods: ['GET', 'POST'])]
    #[Template('sales/competitors/list.html.twig')]
    public function list(Request $request)
    {
        $competitorDatatable = $this->createDataTable(CompetitorDataTableType::class, 'sales/competitors');
        $competitorDatatable->handleRequest($request);
        if ($competitorDatatable->isExporting() && $competitorDatatable->getQuery() instanceof ApiProxyQuery) {
            return $competitorDatatable->getQuery()->export();
        }

        return [
            'competitorDatatable' => $competitorDatatable->createView(),
        ];
    }

    #[Route(path: '/competitors/add', name: 'sales_competitors_add', methods: ['GET', 'POST'])]
    #[Template('sales/competitors/add.html.twig')]
    #[IsGranted('FEATURE_COMPETITOR_CREATE')]
    public function add(Request $request)
    {
        $competitorForm = $this
            ->formFactory
            ->createNamed('competitor_form', CompetitorType::class)
        ;

        $competitorForm->handleRequest($request);
        if ($competitorForm->isSubmitted() && $competitorForm->isValid()) {
            try {
                $data = $competitorForm->getData();
                $logo = $competitorForm->get('logo')->getData();

                $newCompetitor = $this->client->save(self::COMPETITORS_URL, $data);

                if (null !== $logo['file']) {
                    $this->fileManager->uploadFile($newCompetitor, $logo['file'], self::COMPETITORS_URL, null, 'logo');
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('competitors.messages.success.add', [], 'sales_competitors')
                );

                return $this->redirectToRoute('sales_competitors_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $competitorForm);
            }
        }

        return [
            'form' => $competitorForm->createView(),
        ];
    }

    #[Route(path: '/competitors/{id}/show', name: 'sales_competitors_show', methods: ['GET'])]
    #[Template('sales/competitors/show.html.twig')]
    public function show(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/competitors'])] ApiData $competitor,
        #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_types'])] HydraCollection $productTypes)
    {
        return [
            'marketIntelligences' => $this->client->findBy('sales/market_intelligences',
                [
                    'competitors' => $competitor['@id'],
                    'itemsPerPage' => 5,
                    'order' => ['id' => 'desc'],
                ]),
            'competitor' => $competitor,
            'productTypes' => $productTypes,
            'forecastClosures' => $this->client->findBy('sales/forecast_closures',
                [
                    'competitor' => $competitor['@id'],
                    'itemsPerPage' => 5,
                    'order' => ['id' => 'desc'],
                ]),
            'competitorPricings' => $this->client->findBy('sales/competitor_pricings',
                [
                    'competitor' => $competitor['@id'],
                    'itemsPerPage' => 5,
                    'order' => ['id' => 'desc'],
                ]),
        ];
    }

    #[Route(path: '/competitors/{id}/edit', name: 'sales_competitors_edit', methods: ['GET|POST'])]
    #[Template('sales/competitors/edit.html.twig')]
    #[IsGranted('FEATURE_COMPETITOR_EDIT')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/competitors'])] ApiData $competitor, Request $request)
    {
        $competitorForm = $this
            ->formFactory
            ->createNamed(
                'competitor_form',
                CompetitorType::class,
                $competitor
            )
        ;

        $competitorForm->handleRequest($request);
        if ($competitorForm->isSubmitted() && $competitorForm->isValid()) {
            try {
                $data = $competitorForm->getData();
                $logo = $competitorForm->get('logo')->getData();

                $newCompetitor = $this->client->save(self::COMPETITORS_URL, $data);
                $fileId = $competitor['logo']['id'] ?? null;
                $this->fileManager->updateImage($competitor, self::COMPETITORS_URL, $logo, 'logo', $fileId);

                $this->addFlash(
                    'success',
                    $this->translator->trans('competitors.messages.success.edit', [], 'sales_competitors')
                );

                return $this->redirectToRoute('sales_competitors_show', ['id' => Iri::id($newCompetitor)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $competitorForm);
            }
        }

        return [
            'competitor' => $competitor,
            'form' => $competitorForm->createView(),
        ];
    }

    #[Route(path: '/competitors/{id}/delete_confirmation_modal', name: 'sales_competitors_delete_confirm', methods: ['GET'])]
    #[IsGranted('FEATURE_COMPETITOR_DELETE')]
    #[Template('sales/competitors/modal/remove_confirmation.html.twig')]
    public function deleteConfirmation(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/competitors'])] ApiData $competitor): array
    {
        return [
            'competitor' => $competitor,
        ];
    }

    #[Route(path: '/competitors/{id}/delete', name: 'sales_competitors_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_COMPETITOR_DELETE')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/competitors'])] ApiData $competitor): RedirectResponse
    {
        try {
            $this->client->remove(self::COMPETITORS_URL, $competitor->getIriId());
        } catch (ClientException $e) {
            if (Response::HTTP_UNPROCESSABLE_ENTITY === $e->getResponse()->getStatusCode()) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('competitors.messages.error.unprocessable', [], 'sales_competitors')
                );

                return $this->redirectToRoute('sales_competitors_home');
            }

            throw $e;
        }

        $this->addFlash(
            'success',
            $this->translator->trans('competitors.messages.success.delete', [], 'sales_competitors')
        );

        return $this->redirectToRoute('sales_competitors_home');
    }

    #[Route(path: '/competitors/{id}/files', name: 'sales_competitors_files', methods: ['GET', 'POST'], defaults: ['label' => 'menu.files', 'domain' => 'messages'])]
    #[Template('sales/competitors/files.html.twig')]
    public function files(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/competitors'])] ApiData $competitor, Request $request)
    {
        $form = $this->formFactory->createNamed('competitor', SimpleFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile($competitor, $file, self::COMPETITORS_URL, $form->get('description')->getData());
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('competitors.messages.success.file', [], 'sales_competitors')
                );

                return $this->redirectToRoute('sales_competitors_files', ['id' => $competitor->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'competitor' => $competitor,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/competitors/{competitorId}/files/{id}/delete', name: 'sales_competitors_delete_file', methods: ['GET'])]
    #[IsGranted('FEATURE_COMPETITOR_EDIT')]
    public function deleteFile(Request $request, $competitorId, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_competitor_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('sales_customers_files', ['id' => $competitorId]);
        }
        $operation = \sprintf('files/%s', $id);
        $this->client->request(self::COMPETITORS_URL, $competitorId, $operation, Request::METHOD_DELETE);

        return $this->redirectToRoute('sales_competitors_files', ['id' => $competitorId]);
    }

    #[Route(path: '/competitors/{competitorId}/files/{id}', name: 'sales_competitors_files_show', methods: 'GET')]
    public function showFile($competitorId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('sales/competitors/%s/files/%s', $competitorId, $id));
    }
}
