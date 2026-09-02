<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Sales\EquipmentShippingRecord\PlanningDailyExceptionType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/planning_daily_exception', defaults: ['alvest_module' => 'ESR'])]
class PlanningDailyExceptionController extends AbstractController
{
    public const RESOURCE_URL = 'sales/planning_daily_exceptions';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, FormFactoryInterface::class, TranslatorInterface::class]);
    }

    #[Route(path: '/{id}/edit', name: 'planning_daily_exception_edit', methods: ['GET|POST'])]
    #[Template('/sales/planning_daily_exceptions/write.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PLANNING_DAILY_LIMIT_WRITE')"))]
    public function write(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ?ApiData $planningDailyException = null)
    {
        $form = $this->container->get(FormFactoryInterface::class)->create(PlanningDailyExceptionType::class, $planningDailyException, [
            'factory' => $planningDailyException['factory']['@id'],
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('planning_daily_exception.edit.success', [], 'equipment_shipping_record')
                );
                $data = $form->getData();

                return $this->redirectToRoute('equipment_shipping_record_planning', ['smw_filter' => ['location' => $data['factory']]]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                $this->addFlash(
                    'warning',
                    $this->container->get(TranslatorInterface::class)->trans('planning_daily_exception.edit.error', [], 'equipment_shipping_record')
                );
            }
        }

        return [
            'planningDailyExceptionForm' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'planning_daily_exception_delete', methods: ['GET|DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_PLANNING_DAILY_LIMIT_WRITE')"))]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $planningDailyException): RedirectResponse
    {
        $factoryIri = $planningDailyException['factory']['@id'] ?? null;

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $planningDailyException->getIriId());

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('planning_daily_exception.delete.success', [], 'equipment_shipping_record')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('planning_daily_exception.delete.error', [], 'equipment_shipping_record')
            );
        }

        return $this->redirectToRoute('equipment_shipping_record_planning', [
            'smw_filter' => ['location' => $factoryIri],
        ]);
    }
}
