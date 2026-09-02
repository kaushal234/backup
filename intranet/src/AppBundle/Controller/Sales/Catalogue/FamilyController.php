<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales\Catalogue;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
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

#[Route(path: '/sales/catalogue/families', defaults: ['alvest_module' => 'CAT', 'breadcrumb_label' => 'Catalogue families', 'moduleDomain' => 'sales_catalogue_family', 'has_home' => 0])]
class FamilyController extends AbstractController
{
    public const NTOS = 'towbarless aircraft tractors';

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

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
    }

    #[Route(path: '/{id}/{option}', name: 'sales_catalogue_family_show', methods: ['GET|POST'], requirements: ['option' => 'show|show-all', 'id' => '\d+'], defaults: ['option' => 'show', 'label' => 'test'])]
    #[Template('sales/catalogue/family.html.twig')]
    public function showFamily(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_families'])] ApiData $productFamily, Request $request, $option)
    {
        $products = $productFamily['products'];

        $leadTimes = $this->client->findBy('lead_times', ['productFamily' => $productFamily['id']]);

        if ('show' === $option) {
            $products = array_filter($products, static function ($product) {
                return !$product['hidden'];
            });
        }

        if ('show-all' === $option
            && !$this->isGranted('ACL_ROLE_PSM')
            && !$this->isGranted('ACL_SUPERUSER')
        ) {
            $this->addFlash('error',
                $this->translator->trans('catalogue.products.hidden_error', [], 'catalogue')
            );

            return $this->redirectToRoute('sales_catalogue_family_show', ['id' => Iri::id($productFamily)]);
        }

        $nto = self::NTOS !== mb_strtolower($productFamily['productType']['englishName']) ? false : true;

        $dms = $this->client->findBy('sales/product_family_dms', ['family' => $productFamily['@id']]);

        $form = $this
            ->formFactory
            ->createNamed('family_dms_form', CatalogueDMSType::class)
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $data['family'] = $productFamily['@id'];

                $this->client->post('sales/product_family_dms',
                    [
                        'json' => $data,
                    ]
                );

                $this->addFlash(
                    'success',
                    $this->translator->trans('catalogue.dms.edit.success', [], 'catalogue')
                );

                return $this->redirectToRoute('sales_catalogue_family_show', ['id' => $productFamily['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'productFamily' => $productFamily,
            'products' => $products,
            'nto' => $nto,
            'form' => $form->createView(),
            'dms' => $dms,
            'leadTimes' => $leadTimes->getSimpleArrayCopy(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'sales_catalogue_edit_family', methods: ['GET|POST'])]
    #[Template('sales/catalogue/edit_family.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_FAMILY_EDIT') or is_granted('MOO_CAT')"))]
    public function editFamily(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_families'])] ApiData $productFamily)
    {
        return [
        ];
    }

    #[Route(path: '/{id}/delete', name: 'sales_families_delete', methods: ['GET|DELETE'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_DELETE') or is_granted('MOO_CAT')"))]
    public function deleteFamily($id)
    {
        $family = $this->client->find('sales/product_families', $id);

        try {
            $this->client->remove('sales/product_families', $id);

            $this->addFlash(
                'success',
                $this->translator->trans('catalogue.family.delete.success', [], 'catalogue')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('catalogue.family.delete.error', [], 'catalogue')
            );

            return $this->redirectToRoute('sales_catalogue_family_show', ['id' => $id]);
        }

        return $this->redirectToRoute('sales_catalogue_type_show', ['id' => $family['productType']['id']]);
    }

    #[Route(path: '/{id}/add_product', name: 'sales_catalogue_add_product', methods: ['GET|POST'])]
    #[Template('sales/catalogue/add_product.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_CREATE') or is_granted('MOO_CAT')"))]
    public function addProduct(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_families'])] ApiData $productFamily)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'product_form',
                ProductType::class,
                [],
                [
                    'family' => $productFamily['@id'],
                    'add' => true,
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
                    $this->translator->trans('catalogue.products.add.success', [], 'catalogue')
                );

                return $this->redirectToRoute('sales_catalogue_family_show', ['id' => $productFamily['id']]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'family' => $productFamily,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/product_dms/{id}/delete', name: 'sales_catalogue_delete_family_dms', methods: ['GET|DELETE'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_CATALOG_CREATE') or is_granted('MOO_CAT')"))]
    public function deleteProductFamilyDMS(#[ApiValueResolverAttribute(parameters: ['resource' => 'sales/product_family_dms'])] ApiData $productFamilyDms, Request $request)
    {
        if (!$this->isCsrfTokenValid('delete', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete dms: please refresh your page.');
        }

        try {
            $this->client->remove('sales/product_family_dms', $productFamilyDms['id']);

            $this->addFlash(
                'success',
                $this->translator->trans('catalogue.dms.delete.success_family', [], 'catalogue')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('catalogue.dms.delete.error_family', [], 'catalogue')
            );
        }

        $id = $productFamilyDms['family']['id'];

        return $this->redirectToRoute('sales_catalogue_family_show', ['id' => $id]);
    }
}
