<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\SupplierRanking;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Form\Type\Purchasing\SupplierRanking\PeriodicityType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('FEATURE_SUPPLIER_RANKING_ADMIN')]
#[Route(path: '/purchasing/supplier-rankings/periodicity', defaults: ['alvest_module' => 'SRM', 'breadcrumb_label' => 'menu.supplier_ranking.title', 'moduleDomain' => 'supplier_rankings'])]
class PeriodicityController extends AbstractController
{
    public const RESOURCE_URL = 'purchasing/supplier_ranking/periodicities';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            FormFactoryInterface::class,
            TranslatorInterface::class,
            ViolationMapper::class,
        ]);
    }

    #[Template('purchasing/supplier_ranking/periodicity/index.html.twig')]
    #[Route(path: '', name: 'periodicity_rankings_home', methods: 'GET')]
    public function home()
    {
        return ['periodicities' => $this->container->get(Client::class)->findBy(self::RESOURCE_URL)];
    }

    #[Template('purchasing/supplier_ranking/periodicity/write.html.twig')]
    #[Route(path: '/add', name: 'periodicity_rankings_add', methods: ['GET', 'POST'])]
    #[Route(path: '/expertiseLevel={expertiseLevelId};classification={classificationId}/edit', name: 'periodicity_rankings_edit', methods: ['GET', 'POST'])]
    public function write(Request $request, ?int $expertiseLevelId, ?int $classificationId)
    {
        $periodicity = null;
        if (null !== $expertiseLevelId && null !== $classificationId) {
            $periodicity = $this->container->get(Client::class)->find(self::RESOURCE_URL, \sprintf('expertiseLevel=%s;classification=%s', $expertiseLevelId, $classificationId));
        }
        $form = $this->container->get(FormFactoryInterface::class)
            ->createNamed(
                'form_periodicity',
                PeriodicityType::class,
                $periodicity
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('periodicity.write.success', [], 'supplier_ranking'));

                return $this->redirectToRoute('periodicity_rankings_home');
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('periodicity.write.error', [], 'supplier_ranking'));
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/expertiseLevel={expertiseLevelId};classification={classificationId}/delete', name: 'periodicity_rankings_delete', methods: ['GET|POST'])]
    public function delete(?int $expertiseLevelId, ?int $classificationId)
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, \sprintf('expertiseLevel=%s;classification=%s', $expertiseLevelId, $classificationId));
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('periodicity.delete.success', [], 'supplier_ranking'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('periodicity.delete.error', [], 'supplier_ranking'));
        }

        return $this->redirectToRoute('periodicity_rankings_home');
    }
}
