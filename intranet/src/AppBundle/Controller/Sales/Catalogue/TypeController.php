<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\Catalogue;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Support\EquipmentSerialsController;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/catalogue/types', defaults: ['alvest_module' => 'CAT', 'breadcrumb_label' => 'Catalogue types', 'moduleDomain' => 'sales_catalogue_type'])]
class TypeController extends AbstractController
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

    #[Route(path: '', name: 'sales_catalogue_type_home', methods: ['GET'])]
    #[Template('sales/catalogue/home.html.twig')]
    public function showProductTypes(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_types', 'filters' => ['order' => ['englishName' => 'ASC']]])] HydraCollection $productTypes)
    {
        return [
            'productTypes' => $productTypes->all(),
        ];
    }

    #[Route(path: '/{id}/{type}', name: 'sales_catalogue_type_show', methods: ['GET'], requirements: ['type' => 'show|show-all', 'id' => '\d+'], defaults: ['type' => 'show'])]
    #[Template('sales/catalogue/type.html.twig')]
    public function showAllProductType(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_types'])] ApiData $productType, $type)
    {
        if ('show' === $type) {
            $productType['productFamilies'] = array_filter($productType['productFamilies'], static function ($family) {
                return !$family['hidden'];
            });
        }

        return compact('productType');
    }

    #[Route(path: '/add', name: 'sales_catalogue_type_add', methods: ['GET|POST'])]
    #[Template('sales/catalogue/add_type.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_TYPE_CREATE') or is_granted('MOO_CAT')"))]
    public function addType(Request $request)
    {
        $form = $this->formFactory->createNamed('product_type_form', ProductTypeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $this->client->save('sales/product_types', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('catalogue.type.add.success', [], 'catalogue')
                );

                return $this->redirectToRoute('sales_catalogue_type_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'sales_catalogue_type_edit', methods: ['GET|POST'])]
    #[Template('sales/catalogue/edit_type.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_TYPE_CREATE') or is_granted('MOO_CAT')"))]
    public function editType(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_types'])] ApiData $productType)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'product_type_form',
                ProductTypeType::class,
                $productType
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $this->client->save('sales/product_types', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('catalogue.type.edit.success', [], 'catalogue')
                );

                return $this->redirectToRoute('sales_catalogue_type_show', ['id' => $productType['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'productType' => $productType,
        ];
    }

    #[Route(path: '/{id}/add_family', name: 'sales_catalogue_family_add', methods: ['GET|POST'])]
    #[Template('sales/catalogue/add_family.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_CREATE') or is_granted('MOO_CAT')"))]
    public function addFamily(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_types'])] ApiData $productType)
    {
        return [];
    }

    #[Route(path: '/{id}/download-er-by-customer-buyer', name: 'sales_catalogue_download_er_buyer_customer', methods: ['GET'])]
    #[IsGranted('FEATURE_CATALOG_DOWNLOAD')]
    public function downloadERByBuyerCustomer(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_types'])] ApiData $productType)
    {
        $parameters = [
            'product.family.productType' => $productType['@id'],
            'normalization_groups_override' => ['equipment_list_buyer'],
            'context' => ['datetime_format' => 'Y-m-d'],
            'itemsPerPage' => 1000,
        ];

        return $this->csvStreamedResponseFactory->create(EquipmentSerialsController::EQUIPMENT_RECORD_URL, $parameters);
    }

    #[Route(path: '/{id}/download-er-by-customer-user', name: 'sales_catalogue_download_er_user_customer', methods: ['GET'])]
    #[IsGranted('FEATURE_CATALOG_DOWNLOAD')]
    public function downloadERByUserCustomer(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_types'])] ApiData $productType)
    {
        $parameters = [
            'product.family.productType' => $productType['@id'],
            'normalization_groups_override' => ['equipment_list_user'],
            'context' => ['datetime_format' => 'Y-m-d'],
            'itemsPerPage' => 1000,
        ];

        return $this->csvStreamedResponseFactory->create(EquipmentSerialsController::EQUIPMENT_RECORD_URL, $parameters);
    }

    #[Route(path: '/{id}/download-er-by-product-user', name: 'sales_catalogue_download_by_er_product_user', methods: ['GET'])]
    #[Route(path: '/{id}/download-er-by-product-buyer', name: 'sales_catalogue_download_by_er_product_buyer', methods: ['GET'])]
    #[Route(path: '/{id}/download-er-count-customer-user', name: 'sales_catalogue_download_er_count_user_customer', methods: ['GET'])]
    #[Route(path: '/{id}/download-er-count-customer-buyer', name: 'sales_catalogue_download_er_count_buyer_customer', methods: ['GET'])]
    #[IsGranted('FEATURE_CATALOG_DOWNLOAD')]
    public function downloadCountERByProductUser(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_types'])] ApiData $productType, $_route)
    {
        $customer = false === mb_strpos($_route, 'buyer') ? 'endUser' : 'buyer';
        $y = 'units';
        $options = [];
        if (false !== mb_strpos($_route, 'by_er_product')) {
            $y = 'product.name';
            $options = [
                'product.family.productType' => $productType['@id'],
                'product.hidden' => 0,
            ];
        }

        $requestUrl = "reports/resource=/equipment_records;x=$customer.name;y=$y";

        $response = new StreamedResponse(function () use ($requestUrl, $options) {
            $report = $this->client->request($requestUrl, null, null, Request::METHOD_GET, [
                'query' => [
                    'options' => array_merge(['hideTotals' => 'both'], $options),
                ],
                'headers' => ['Accept' => 'text/csv'],
            ]);

            echo $report->getContent();
        });

        $response->headers->set('Content-Disposition', 'inline; filename=data.csv');
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');

        return $response;
    }
}
