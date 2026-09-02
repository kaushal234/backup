<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\SupplierRanking;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Purchasing\SupplierRanking\ExpertiseLevelTransferType;
use AppBundle\Form\Type\Purchasing\SupplierRanking\ExpertiseLevelType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('FEATURE_SUPPLIER_RANKING_ADMIN')]
#[Route(path: '/purchasing/supplier-rankings/expertise-level', defaults: ['alvest_module' => 'SRM', 'breadcrumb_label' => 'menu.supplier_ranking.title', 'moduleDomain' => 'supplier_rankings'])]
class ExpertiseLevelController extends AbstractController
{
    public const RESOURCE_URL = 'purchasing/supplier_ranking/expertise_levels';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            FormFactoryInterface::class,
            TranslatorInterface::class,
            ViolationMapper::class,
        ]);
    }

    #[Route(path: '', name: 'expertise_level_rankings_home', methods: 'GET')]
    #[Template('purchasing/supplier_ranking/expertise_level/index.html.twig')]
    public function home()
    {
        return ['expertiseLevels' => $this->container->get(Client::class)->findBy(self::RESOURCE_URL)];
    }

    #[Route(path: '/add', name: 'expertise_level_rankings_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'expertise_level_rankings_edit', methods: ['GET', 'POST'])]
    #[Template('purchasing/supplier_ranking/expertise_level/write.html.twig')]
    public function write(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $expertiseLevel = null)
    {
        $form = $this->container->get(FormFactoryInterface::class)
            ->createNamed(
                'form_expertise',
                ExpertiseLevelType::class,
                $expertiseLevel
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('expertise_level.write.success', [], 'supplier_ranking'));

                return $this->redirectToRoute('expertise_level_rankings_home');
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('expertise_level.write.error', [], 'supplier_ranking'));
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/transfer', name: 'expertise_level_rankings_transfer', methods: ['GET|POST'])]
    #[Template('purchasing/supplier_ranking/expertise_level/transfer.html.twig')]
    public function transfer(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $expertiseLevel)
    {
        $form = $this->container->get(FormFactoryInterface::class)->createNamed('', ExpertiseLevelTransferType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (null !== $data['target']) {
                try {
                    $this->container->get(Client::class)->request(self::RESOURCE_URL, $expertiseLevel->getIriId(), 'transfer', 'PUT',
                        [
                            'json' => [
                                'target' => $data['target'],
                            ],
                        ]);
                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('expertise_level.transfer.success_transfer', [], 'supplier_ranking'));
                } catch (ClientException $e) {
                    $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('expertise_level.transfer.error_transfer', [], 'supplier_ranking'));
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);

                    return $this->redirectToRoute('expertise_level_rankings_home');
                }
            }
            if (true === $data['delete']) {
                try {
                    $this->container->get(Client::class)->remove(self::RESOURCE_URL, $expertiseLevel->getIriId());
                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('expertise_level.transfer.success_delete', [], 'supplier_ranking'));
                } catch (ClientException $e) {
                    $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('expertise_level.transfer.error_delete', [], 'supplier_ranking'));
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                }
            }

            return $this->redirectToRoute('expertise_level_rankings_home');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
