<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Mis\Application\ApplicationType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/applications', defaults: ['alvest_module' => 'MOD', 'breadcrumb_label' => 'menu.application.title', 'moduleDomain' => ApplicationController::TRANSLATION_DOMAIN])]
class ApplicationController extends AbstractController
{
    public const RESOURCE_URL = 'mis/applications';
    public const TRANSLATION_DOMAIN = 'mis_application';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            ViolationMapper::class,
        ]);
    }

    #[Route(path: '', name: 'mis_application_home', methods: 'GET')]
    #[Template('mis/application/home.html.twig')]
    public function index(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] HydraCollection $applications)
    {
        return compact('applications');
    }

    #[Route(path: '/add', name: 'mis_application_add', methods: 'GET|POST')]
    #[Template('mis/application/write.html.twig')]
    #[IsGranted('FEATURE_APPLICATION_CREATE')]
    public function add(Request $request)
    {
        $form = $this->createForm(ApplicationType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $data);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis_application.success.add', [], self::TRANSLATION_DOMAIN));

                return $this->redirectToRoute('mis_application_home');
            } catch (ClientException $exception) {
                $this->container->get(ViolationMapper::class)->mapToForm($exception, $form);
                $this->addFlash('error', \sprintf('%s. Reason: %s', $this->container->get(TranslatorInterface::class)->trans('mis_application.error.add', [], self::TRANSLATION_DOMAIN), $exception->getMessage()));
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', requirements: ['id' => '\d+'], name: 'mis_application_edit', methods: 'GET|POST')]
    #[Template('mis/application/write.html.twig')]
    #[IsGranted('FEATURE_APPLICATION_EDIT')]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $application, Request $request)
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $data);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis_application.success.edit', [], self::TRANSLATION_DOMAIN));

                return $this->redirectToRoute('mis_application_home');
            } catch (ClientException $exception) {
                $this->container->get(ViolationMapper::class)->mapToForm($exception, $form);
                $this->addFlash('error', \sprintf('%s. Reason: %s', $this->container->get(TranslatorInterface::class)->trans('mis_application.error.edit', [], self::TRANSLATION_DOMAIN), $exception->getMessage()));
            }
        }

        return [
            'form' => $form->createView(),
            'application' => $application,
        ];
    }

    #[Route(path: '/{id}/show', name: 'mis_application_show', methods: 'GET')]
    public function show()
    {
        return $this->redirectToRoute('mis_application_home');
    }
}
