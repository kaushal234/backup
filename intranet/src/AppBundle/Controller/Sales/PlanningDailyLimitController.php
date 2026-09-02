<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Sales\EquipmentShippingRecord\PlanningDailyLimitType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/planning', defaults: ['alvest_module' => 'ESR'])]
class PlanningDailyLimitController extends AbstractController
{
    public const string RESOURCE_URL = 'sales/planning_daily_limits';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class, ViolationMapper::class]);
    }

    #[Route(path: '', name: 'planning_daily_limit_home', methods: ['GET'])]
    #[Template('/sales/planning_daily_limits/home.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PLANNING_DAILY_LIMIT_READ')"))]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] HydraCollection $planningDailyLimits)
    {
        return [
            'planningDailyLimits' => $planningDailyLimits,
        ];
    }

    #[Route(path: '/add', name: 'planning_daily_limit_add', methods: ['GET|POST'])]
    #[Route(path: '/{id}/edit', name: 'planning_daily_limit_edit', methods: ['GET|POST'])]
    #[Template('/sales/planning_daily_limits/write.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PLANNING_DAILY_LIMIT_WRITE')"))]
    public function write(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $planningDailyLimit = null)
    {
        $form = $this->container->get('form.factory')->create(PlanningDailyLimitType::class, $planningDailyLimit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('planning_daily_limit.edit.success', [], 'equipment_shipping_record')
                );

                return $this->redirectToRoute('planning_daily_limit_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                $this->addFlash(
                    'warning',
                    $this->container->get(TranslatorInterface::class)->trans('planning_daily_limit.edit.error', [], 'equipment_shipping_record')
                );
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'planning_daily_limit_delete', methods: ['GET|DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PLANNING_DAILY_LIMIT_WRITE')"))]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $planningDailyLimit): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $planningDailyLimit->getIriId());

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('planning_daily_limit.delete.success', [], 'equipment_shipping_record')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('planning_daily_limit.delete.error', [], 'equipment_shipping_record')
            );
        }

        return $this->redirectToRoute('planning_daily_limit_home');
    }
}
