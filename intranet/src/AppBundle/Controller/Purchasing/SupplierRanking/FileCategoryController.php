<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\SupplierRanking;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Purchasing\SupplierRanking\FileCategoryTransferType;
use AppBundle\Form\Type\Purchasing\SupplierRanking\FileCategoryType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/supplier-rankings/file_category', defaults: ['alvest_module' => 'SRM', 'breadcrumb_label' => 'menu.supplier_ranking.title', 'moduleDomain' => 'supplier_rankings'])]
#[IsGranted('FEATURE_SUPPLIER_RANKING_ADMIN')]
class FileCategoryController extends AbstractController
{
    public const RESOURCE_URL = 'purchasing/supplier_ranking/file_categories';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            FormFactoryInterface::class,
            TranslatorInterface::class,
            ViolationMapper::class,
        ]);
    }

    #[Route(name: 'file_category_rankings_home', methods: 'GET')]
    #[Template('purchasing/supplier_ranking/file_category/index.html.twig')]
    public function home()
    {
        return ['fileCategories' => $this->container->get(Client::class)->findBy(self::RESOURCE_URL)];
    }

    #[Route(path: '/add', name: 'file_category_rankings_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'file_category_rankings_edit', methods: ['GET', 'POST'])]
    #[Template('purchasing/supplier_ranking/file_category/write.html.twig')]
    public function write(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $fileCategory = null)
    {
        $form = $this->container->get(FormFactoryInterface::class)
            ->createNamed(
                'form_category',
                FileCategoryType::class,
                $fileCategory
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('file_category.write.success', [], 'supplier_ranking'));

                return $this->redirectToRoute('file_category_rankings_home');
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('file_category.write.error', [], 'supplier_ranking'));
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/transfer', name: 'file_category_rankings_transfer', methods: ['GET', 'POST'])]
    #[Template('purchasing/supplier_ranking/file_category/transfer.html.twig')]
    public function transfer(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $fileCategory)
    {
        $form = $this->container->get(FormFactoryInterface::class)->createNamed('', FileCategoryTransferType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (null !== $data['target']) {
                try {
                    $this->container->get(Client::class)->request(self::RESOURCE_URL, $fileCategory->getIriId(), 'transfer', 'PUT',
                        [
                            'json' => [
                                'target' => $data['target'],
                            ],
                        ]);
                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('file_category.transfer.success_transfer', [], 'supplier_ranking'));
                } catch (ClientException $e) {
                    $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('file_category.transfer.error_transfer', [], 'supplier_ranking'));
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);

                    return $this->redirectToRoute('file_category_rankings_home');
                }
            }
            if (true === $data['delete']) {
                try {
                    $this->container->get(Client::class)->remove(self::RESOURCE_URL, $fileCategory->getIriId());
                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('file_category.transfer.success_delete', [], 'supplier_ranking'));
                } catch (ClientException $e) {
                    $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('file_category.transfer.error_delete', [], 'supplier_ranking'));
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                }
            }

            return $this->redirectToRoute('file_category_rankings_home');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
