<?php

declare(strict_types=1);

namespace AppBundle\Controller\Engineering\Pictogram;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Engineering\Pictogram\CategoryDataTableType;
use AppBundle\Form\Type\Engineering\Pictogram\CategoryType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('FEATURE_PICTOGRAM_CATEGORY_READ')]
#[Route(path: '/engineering/pictograms/categories', defaults: ['alvest_module' => 'HMI Pictograms', 'breadcrumb_label' => 'menu.pictogram_categories', 'moduleDomain' => 'pictogram_category'])]
class CategoryController extends AbstractController
{
    public const string RESOURCE_URL = 'engineering/pictogram/categories';
    public const string DELETE_TOKEN = 'delete_pictogram_category_id';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            ViolationMapper::class,
        ]);
    }

    #[Route(path: '/', name: 'pictogram_category_home')]
    public function index(
        Request $request,
        DataTableFactoryInterface $dataTableFactory,
    ): Response {
        $datatable = $dataTableFactory->create(CategoryDataTableType::class, CategoryDataTableType::RESOURCE);

        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return $this->render('engineering/pictogram/category/index.html.twig', [
            'categoryDataTable' => $datatable->createView(),
        ]);
    }

    #[Route(path: '/add', name: 'pictogram_category_add', methods: ['GET|POST'])]
    #[IsGranted('FEATURE_PICTOGRAM_CATEGORY_CREATE')]
    public function create(Request $request): Response
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $form = $this->createForm(CategoryType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $translator->trans('category.create.success', [], 'engineering_pictogram'));

                return $this->redirectToRoute('pictogram_category_home');
            } catch (ClientException $e) {
                $this->addFlash('error', $translator->trans('category.create.error', [], 'engineering_pictogram'));
                $violationMapper->mapToForm($e, $form);
            }
        }

        return $this->render('engineering/pictogram/category/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/show', name: 'pictogram_category_show', methods: ['GET|POST'])]
    #[Route(path: '/{id}/edit', name: 'pictogram_category_edit', methods: ['GET|POST'])]
    #[IsGranted('FEATURE_PICTOGRAM_CATEGORY_UPDATE')]
    public function update(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])]
        ?ApiData $category = null,
    ): Response {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $translator->trans('category.update.success', [], 'engineering_pictogram'));

                return $this->redirectToRoute('pictogram_category_home');
            } catch (ClientException $e) {
                $this->addFlash('error', $translator->trans('category.update.error', [], 'engineering_pictogram'));
                $violationMapper->mapToForm($e, $form);
            }
        }

        return $this->render('engineering/pictogram/category/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'pictogram_category_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_PICTOGRAM_CATEGORY_DELETE')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $category): RedirectResponse
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);

        try {
            $client->remove(self::RESOURCE_URL, $category->getIriId());

            $this->addFlash('success', $translator->trans('category.delete.success', [], 'engineering_pictogram'));
        } catch (ClientException $e) {
            $this->addFlash('error', $translator->trans('category.delete.error', [], 'engineering_pictogram'));
        }

        return $this->redirectToRoute('pictogram_category_home');
    }
}
