<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\Catalogue;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Sales\ProductCertificateFilterType;
use AppBundle\Form\Type\Sales\Catalogue\ProductCertificateType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Form\Type\SimpleSearchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/catalogue/certificates', defaults: ['alvest_module' => 'CAT', 'breadcrumb_label' => 'Catalogue certificates', 'moduleDomain' => 'sales_product_certificates'])]
class CertificateController extends AbstractController
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var ViolationMapper
     */
    private $violationMapper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var FileStreamedResponseFactory
     */
    private $fileStreamedResponseFactory;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, FileStreamedResponseFactory $fileStreamedResponseFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
    }

    #[Route(path: '', name: 'sales_product_certificates_home', methods: ['GET'])]
    #[Template('sales/catalogue/product_certificate/home.html.twig')]
    public function showCertificates(Request $request)
    {
        $reportTitle = 'catalogue.certificates.latest';
        $searchTable = false;
        $pagination = false;

        $parameters = [];
        $parameters['page'] = $request->query->getInt('page', 1);
        $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['expiredAt' => 'asc'];
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', 10);

        $simpleSearchForm = $this->createForm(SimpleSearchType::class, [], [
            'method' => 'GET',
            'csrf_protection' => false,
            'search_label' => false,
            'search_placeholder' => 'Search for...',
        ]);

        $simpleSearchForm->handleRequest($request);
        if ($simpleSearchForm->isSubmitted() && $simpleSearchForm->isValid()) {
            $reportTitle = 'catalogue.certificates.filtered';
            $searchTable = true;
            $pagination = 20;
            $parameters = [
                'q' => $simpleSearchForm->get('q')->getData(),
                'itemsPerPage' => 500,
            ];
        }

        $formFilter = $this
            ->formFactory
            ->createNamed(
                '',
                ProductCertificateFilterType::class, [],
                [
                    'action' => $this->generateUrl('sales_product_certificates_home'),
                    'method' => 'GET',
                ]);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = array_merge($parameters, $formFilter->getData());
            $parameters['itemsPerPage'] = 20000;
            $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['expiredAt' => 'asc'];

            if (isset($parameters['productType'])) {
                $parameters['product.family.productType'] = $parameters['productType'];
                unset($parameters['productType']);
            }
            $reportTitle = 'catalogue.certificates.filtered';
            $searchTable = true;
            $pagination = 20;
        }

        $productCertificates = [];
        try {
            $productCertificates = $this->client->findBy('sales/product_certificates', $parameters);
        } catch (ClientException $e) {
            $this->violationMapper->mapToForm($e, $formFilter);
        }

        return [
            'formFilter' => $formFilter->createView(),
            'search_form' => $simpleSearchForm->createView(),
            'itemsPerPage' => $parameters['itemsPerPage'],
            'reportTitle' => $reportTitle,
            'searchTable' => $searchTable,
            'pagination' => $pagination,
            'productCertificates' => $productCertificates,
        ];
    }

    #[Route(path: '/{id}/show', name: 'sales_product_certificates_show', methods: 'GET')]
    #[Template('sales/catalogue/product_certificate/show.html.twig')]
    public function showCertificate(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_certificates'])] ApiData $productCertificate, Request $request)
    {
        $formFile = $this->formFactory->createNamed('file', SimpleFileType::class);
        $formFile->handleRequest($request);
        if ($formFile->isSubmitted() && $formFile->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFile->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $multiPart['file'] = DataPart::fromPath($file->getPathname(), $file->getClientOriginalName());
                    $multiPart['description'] = $formFile->get('description')->getData();
                    $formData = new FormDataPart($multiPart);

                    $this->client->request('sales/product_certificate', $productCertificate->getIriId(), 'files', 'POST', [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('catalogue.certificates.file.success', [], 'catalogue')
                );

                return $this->redirectToRoute('sales_product_certificates_show', ['id' => $productCertificate->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formFile);
            }
        }

        return [
            'productCertificate' => $productCertificate,
            'formFile' => $formFile->createView(),
        ];
    }

    #[Route(path: '/add', name: 'sales_product_certificates_add', methods: 'GET|POST')]
    #[Template('sales/catalogue/product_certificate/add.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')"))]
    public function addCertificate(Request $request)
    {
        $form = $this->formFactory->createNamed('form', ProductCertificateType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->client->save('sales/product_certificates', $data);

                $this->addFlash('success', $this->translator->trans('catalogue.certificates.add.success', [], 'catalogue'));

                return $this->redirectToRoute('sales_product_certificates_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'sales_product_certificates_edit', methods: 'GET|POST')]
    #[Template('sales/catalogue/product_certificate/edit.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')"))]
    public function editCertificate(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_certificates'])] ApiData $productCertificate, Request $request)
    {
        $form = $this->formFactory->createNamed('form', ProductCertificateType::class, $productCertificate);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->client->save('sales/product_certificates', $data);

                $this->addFlash('success', $this->translator->trans('catalogue.certificates.edit.success', [], 'catalogue'));

                return $this->redirectToRoute('sales_product_certificates_show', ['id' => $productCertificate->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'productCertificate' => $productCertificate,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'sales_product_certificates_delete', methods: ['GET|DELETE'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')"))]
    public function deleteCertificate(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_certificates'])] ApiData $productCertificate)
    {
        try {
            $this->client->remove('sales/product_certificates', $productCertificate->getIriId());

            $this->addFlash(
                'success',
                $this->translator->trans('catalogue.certificates.delete.success', [], 'catalogue')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('catalogue.certificates.delete.fail', [], 'catalogue')
            );
        }

        return $this->redirectToRoute('sales_product_certificates_home');
    }

    #[Route(path: '/{productCertificateId}/files/{id}/delete', name: 'sales_delete_product_certificate_file', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PRODUCT_CERTIFICATE_ADMIN') or is_granted('MOO_CAT')"))]
    public function deleteFile(Request $request, $productCertificateId, $id)
    {
        if (!$this->isCsrfTokenValid('delete_product_certificate_file', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete file: please refresh your form.');

            return $this->redirectToRoute('sales_product_certificates_show', ['id' => $productCertificateId]);
        }
        $operation = \sprintf('files/%s', $id);
        $this->client->request('sales/product_certificates', $productCertificateId, $operation, Request::METHOD_DELETE);

        return $this->redirectToRoute('sales_product_certificates_show', ['id' => $productCertificateId]);
    }

    /**
     * @return StreamedResponse
     */
    #[Route(path: '/{productCertificateId}/files/{id}', name: 'sales_product_certificate_files_show', methods: 'GET')]
    public function showFile($productCertificateId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('sales/product_certificates/%s/files/%s', $productCertificateId, $id));
    }
}
