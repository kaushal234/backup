<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\CustomerServiceRecord;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Service\CustomerServiceRecord\InterventionPostType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/service/intervention', defaults: ['alvest_module' => 'CSR', 'breadcrumb_label' => 'menu.intervention.title', 'moduleDomain' => 'intervention'])]
class InterventionController extends AbstractController
{
    public const INTERVENTION_URL = 'service/interventions';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class, FormFactoryInterface::class, ViolationMapper::class]);
    }

    #[Route(path: '', name: 'intervention_home', methods: ['GET', 'POST'], defaults: ['label' => 'intervention.menu'])]
    #[Template('service/customer_service_record/intervention_home.html.twig')]
    public function home(Request $request)
    {
        $form = $this->createForm(InterventionPostType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::INTERVENTION_URL, $form->getData());

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('intervention.success.add', [], 'customer_service_record'));

                return $this->redirectToRoute('intervention_home');
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'interventions' => $this->container->get(Client::class)->findBy(self::INTERVENTION_URL),
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/show', requirements: ['id' => '\d+'], name: 'intervention_show', methods: ['GET', 'POST'], defaults: ['label' => 'intervention.breadcrumb'])]
    #[Template('service/customer_service_record/intervention_show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::INTERVENTION_URL])] ApiData $intervention, Request $request)
    {
        $form = $this->createForm(InterventionPostType::class, $intervention);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::INTERVENTION_URL, $form->getData());

                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('intervention.success.update', [], 'customer_service_record'));

                return $this->redirectToRoute('intervention_show', ['id' => $intervention->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'intervention' => $intervention,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', requirements: ['id' => '\d+'], name: 'intervention_delete', methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_CUSTOMER_SERVICE_RECORD_DELETE')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::INTERVENTION_URL])] ApiData $intervention, Request $request)
    {
        if (!$this->isCsrfTokenValid('intervention_delete', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('intervention.errors.delete', [], 'customer_service_record'));

            return $this->redirectToRoute('intervention_home');
        }

        try {
            $this->container->get(Client::class)->remove(self::INTERVENTION_URL, $intervention->getIriId());
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('csr.success.delete', [], 'customer_service_record'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('csr.errors.delete', [], 'customer_service_record'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('intervention_home');
    }
}
