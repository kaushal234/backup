<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\Catalogue;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Sales\Catalogue\CatalogueDMSType;
use AppBundle\Form\Type\Sales\Catalogue\ProductType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/catalogue/products', defaults: ['alvest_module' => 'CAT', 'breadcrumb_label' => 'Catalogue products', 'moduleDomain' => 'sales_catalogue_product'])]
class ProductController extends AbstractController
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
     * @var CsvStreamedResponseFactory
     */
    private $csvStreamedResponseFactory;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, CsvStreamedResponseFactory $csvStreamedResponseFactory)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->csvStreamedResponseFactory = $csvStreamedResponseFactory;
    }

    #[Route(path: '/{visibility}', name: 'sales_catalogue_product_home', methods: ['GET'], requirements: ['visibility' => 'show|show-all'], defaults: ['visibility' => 'show'])]
    #[Template('sales/catalogue/all_products.html.twig')]
    public function showAllProducts($visibility)
    {
        $parameters = [
            'pagination' => false,
        ];

        if ('show' === $visibility) {
            $parameters['hidden'] = false;
        }
        $financeFamilies = $this->client->findBy('finance/finance_families', [], ['name' => 'ASC'], ['raw_results' => true]);
        $products = $this->client->findBy('sales/products', $parameters, ['name' => 'ASC'], ['raw_results' => true]);
        $catalogueProducts = [];
        foreach ($products['hydra:member'] as $product) {
            $catalogueProducts[$product['@id']] = $product;
        }

        return [
            'visibility' => $visibility,
            'initialState' => [
                'finance' => [
                    'financeFamilies' => $financeFamilies['hydra:member'],
                ],
                'product' => [
                    'products' => $catalogueProducts,
                ],
            ],
        ];
    }

    #[Route(path: '/{id}/show', name: 'sales_catalogue_product_show', methods: ['GET|POST'])]
    #[Template('sales/catalogue/product.html.twig')]
    public function showProduct(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/products'])] ApiData $product, Request $request)
    {
        if ($product['hidden']
            && (!$this->isGranted('ACL_ROLE_PSM')
                && !$this->isGranted('ACL_SUPERUSER'))) {
            $this->addFlash('error',
                $this->translator->trans('catalogue.products.hidden_error', [], 'catalogue')
            );

            return $this->redirectToRoute('sales_catalogue_type_home');
        }
        $dms = $this->client->findBy('sales/product_dms', ['product' => $product['@id']]);

        $form = $this
            ->formFactory
            ->createNamed('product_dms_form', CatalogueDMSType::class)
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['product'] = $product['@id'];

                $this->client->post('sales/product_dms',
                    [
                        'json' => $data,
                    ]
                );

                $this->addFlash(
                    'success',
                    $this->translator->trans('catalogue.dms.edit.success', [], 'catalogue')
                );

                return $this->redirectToRoute('sales_catalogue_product_show', ['id' => $product['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'product' => $product,
            'form' => $form->createView(),
            'dms' => $dms,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'sales_catalogue_product_edit', methods: ['GET|POST'])]
    #[Template('sales/catalogue/edit_product.html.twig')]
    #[IsGranted(attribute: 'CATALOG_ADMIN_VOTER', subject: new Expression('args["product"].getIri()'))]
    public function editProduct(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/products'])] ApiData $product)
    {
        $authorizedFields = $this->client->get('/fields', ['query' => ['iri' => $product['@id'], 'method' => 'PUT']]);
        $form = $this
            ->formFactory
            ->createNamed(
                'product_form',
                ProductType::class,
                $product,
                [
                    'family' => $product['family']['@id'],
                    'product_type' => $product['family']['productType']['@id'],
                    'authorizedFields' => $authorizedFields,
                ]
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $this->client->save('sales/products', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('catalogue.products.edit.success', [], 'catalogue')
                );

                return $this->redirectToRoute('sales_catalogue_product_show', ['id' => $product['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'product' => $product,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'sales_products_delete', methods: ['GET|DELETE'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_DELETE') or is_granted('MOO_CAT')"))]
    public function deleteProduct($id)
    {
        $product = $this->client->find('sales/products', $id);

        try {
            $this->client->remove('sales/products', $id);

            $this->addFlash(
                'success',
                $this->translator->trans('catalogue.products.delete.success', [], 'catalogue')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('catalogue.products.delete.error', [], 'catalogue')
            );

            return $this->redirectToRoute('sales_catalogue_family_show', ['id' => $product['family']['id']]);
        }

        return $this->redirectToRoute('sales_catalogue_family_show', ['id' => $product['family']['id']]);
    }

    #[Route(path: '/download-all-products-{visibility}', name: 'sales_catalogue_download_all_products', methods: ['GET'])]
    #[IsGranted('FEATURE_CATALOG_DOWNLOAD')]
    public function downloadAllProducts($visibility)
    {
        $parameters = [
            'normalization_groups_override' => ['product_export'],
            'itemsPerPage' => 1000,
        ];

        if ('show' === $visibility) {
            $parameters['hidden'] = false;
        }

        return $this->csvStreamedResponseFactory->create('sales/products', $parameters);
    }

    #[Route(path: '/product_dms/{id}/delete', name: 'sales_catalogue_delete_product_dms', methods: ['GET|DELETE'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_CREATE') or is_granted('MOO_CAT')"))]
    public function deleteProductDMS(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_dms'])] ApiData $productDMS, Request $request)
    {
        if (!$this->isCsrfTokenValid('delete', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete dms: please refresh your page.');
        }

        try {
            $this->client->remove('sales/product_dms', $productDMS['id']);

            $this->addFlash(
                'success',
                $this->translator->trans('catalogue.dms.delete.success_product', [], 'catalogue')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('catalogue.dms.delete.error_product', [], 'catalogue')
            );
        }

        $id = $productDMS['product']['id'];

        return $this->redirectToRoute('sales_catalogue_product_show', ['id' => $id]);
    }
}
