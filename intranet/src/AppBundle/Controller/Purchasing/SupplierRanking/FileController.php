<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\SupplierRanking;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Purchasing\SupplierRankingFileDataTableType;
use AppBundle\Form\Type\Purchasing\SupplierRanking\SupplierRankingFileType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(attribute: new Expression("is_granted('FEATURE_SUPPLIER_RANKING_READ_ALL') or is_granted('FEATURE_SUPPLIER_RANKING_READ')"))]
#[Route(path: '/purchasing/supplier-rankings', defaults: ['alvest_module' => 'SRM', 'breadcrumb_label' => 'menu.supplier_ranking_files.title', 'moduleDomain' => 'supplier_rankings_files'])]
class FileController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;
    private const RESOURCE_URL = 'purchasing/supplier_ranking/';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            FormFactoryInterface::class,
            FileStreamedResponseFactory::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(path: '/files', name: 'supplier_rankings_files_home')]
    #[Template('purchasing/supplier_ranking/file/index.html.twig')]
    public function index(Request $request): array|Response
    {
        $datatable = $this->createDataTable(
            SupplierRankingFileDataTableType::class,
            SupplierRankingFileDataTableType::RESOURCE,
        );

        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'datatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/files', name: 'supplier_rankings_files_show')]
    #[Template('purchasing/supplier_ranking/file/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'purchasing/supplier_ranking/supplier_rankings'])] ApiData $supplierRanking)
    {
        return [
            'supplierRanking' => $supplierRanking,
        ];
    }

    #[Route(path: '/{id}/files/add', name: 'supplier_rankings_files_add')]
    #[Template('purchasing/supplier_ranking/file/add.html.twig')]
    public function add(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'purchasing/supplier_ranking/supplier_rankings'])] ApiData $supplierRanking)
    {
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $translator = $this->container->get(TranslatorInterface::class);

        $form = $formFactory->create(SupplierRankingFileType::class, [
            'supplierRanking' => $supplierRanking->toArray(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (($file = $form->get('file')->getData()) instanceof UploadedFile) {
                try {
                    $multiPart['file'] = DataPart::fromPath($file->getPathname(), $file->getClientOriginalName());
                    $multiPart['category'] = $form->get('category')->getData();
                    if (null !== $form->get('expiredAt')->getData()) {
                        $multiPart['expiredAt'] = $form->get('expiredAt')->getData();
                    }
                    $formData = new FormDataPart($multiPart);
                    $this->container->get(Client::class)->post(\sprintf(self::RESOURCE_URL.'supplier_rankings/%s/files', Iri::id($form->get('supplierRanking')->getData())), [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);

                    $this->addFlash(
                        'success',
                        $translator->trans('messages.success.edit', [], 'supplier_ranking')
                    );

                    return $this->redirectToRoute('supplier_rankings_files_show', ['id' => $supplierRanking->toArray()['id']]);
                } catch (ClientException $e) {
                    $errorDescription = $e->getResponse()->toArray(false);
                    $this->addFlash('error', $errorDescription['hydra:description']);
                }
            }
        }

        return [
            'supplierRanking' => $supplierRanking,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/files/{fileId}/delete', name: 'supplier_rankings_files_delete')]
    public function delete($id, $fileId)
    {
        $translator = $this->container->get(TranslatorInterface::class);

        try {
            $this->container->get(Client::class)->remove(\sprintf(self::RESOURCE_URL.'supplier_rankings/%s/files', $id), $fileId);
            $this->addFlash('success', $translator->trans('messages.success.delete_file', [], 'supplier_ranking'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $translator->trans('messages.error.delete_file', [], 'supplier_ranking'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('supplier_rankings_files_show', ['id' => $id]);
    }

    #[Route(path: '/{id}/files/{fileId}', name: 'supplier_rankings_files_download', methods: 'GET')]
    public function showFile($id, $fileId): StreamedResponse
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf(self::RESOURCE_URL.'supplier_rankings/%s/files/%s', $id, $fileId));
    }

    #[Route(path: '/files/review', name: 'supplier_rankings_files_review')]
    #[Template('purchasing/supplier_ranking/file/review.html.twig')]
    public function review(Request $request): array|Response
    {
        $completedFilesDataTable = $this->createNamedDataTable(
            'completed_files_data_table',
            SupplierRankingFileDataTableType::class,
            SupplierRankingFileDataTableType::RESOURCE,
            [
                'title' => 'supplier_ranking.data_table.completed_files',
                'themes' => [
                    'datatable_card_theme.html.twig',
                    'purchasing/supplier_ranking/file/_completed_files_datatable_theme.html.twig',
                ],
                'include_completion_review' => true,
            ]
        );

        $completedFilesDataTable->handleRequest($request);

        if ($completedFilesDataTable->isExporting() && $completedFilesDataTable->getQuery() instanceof ApiProxyQuery) {
            return $completedFilesDataTable->getQuery()->export();
        }

        if ($completedFilesDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($completedFilesDataTable);
        }

        $expiredFilesDataTable = $this->createNamedDataTable(
            'expired_files_data_table',
            SupplierRankingFileDataTableType::class,
            SupplierRankingFileDataTableType::RESOURCE,
            [
                'title' => 'supplier_ranking.data_table.expired_files',
            ]
        );

        $expiredFilesDataTable->handleRequest($request);

        if ($expiredFilesDataTable->isExporting() && $expiredFilesDataTable->getQuery() instanceof ApiProxyQuery) {
            return $expiredFilesDataTable->getQuery()->export();
        }

        if ($expiredFilesDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($expiredFilesDataTable);
        }

        return [
            'completedFilesDataTable' => $completedFilesDataTable->createView(),
            'expiredFilesDataTable' => $expiredFilesDataTable->createView(),
        ];
    }
}
