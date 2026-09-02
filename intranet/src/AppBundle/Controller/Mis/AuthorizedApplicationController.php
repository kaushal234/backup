<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Mis\AuthorizedApplicationType;
use AppBundle\Form\Type\Mis\FeatureType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/authorized-applications', defaults: ['alvest_module' => 'MIS'])]
#[IsGranted('FEATURE_AUTHORIZED_APPLICATION_ADMIN')]
class AuthorizedApplicationController extends AbstractController
{
    private const RESOURCE_URL = 'authorized_applications';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            FormFactoryInterface::class,
            TranslatorInterface::class,
        ]);
    }

    #[Route(path: '', name: 'mis_authorized_application_list', methods: ['GET', 'POST'])]
    #[Template('mis/authorized_application/list.html.twig')]
    public function index(#[ApiValueResolverAttribute] HydraCollection $authorizedApplications, Request $request, ViolationMapper $violationMapper)
    {
        $form = $this->container->get(FormFactoryInterface::class)->create(AuthorizedApplicationType::class, $authorizedApplications->getSimpleArrayCopy());

        if ($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            try {
                $authorizedApplication = $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.authorized_applications.messages.creation_success', [
                    '%application%' => $authorizedApplication['name'],
                ], 'mis'));

                $authorizedApplicationsCopy = $authorizedApplications->getSimpleArrayCopy();
                $authorizedApplicationsCopy[] = $authorizedApplication;

                return [
                    'authorizedApplication' => $authorizedApplication,
                    'authorizedApplications' => $authorizedApplicationsCopy,
                ];
            } catch (ClientException $e) {
                $violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'authorizedApplications' => $authorizedApplications,
        ];
    }

    #[Route(path: '/{id}/show', name: 'mis_authorized_application_show', methods: ['GET', 'POST'])]
    #[Template('mis/authorized_application/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $authorizedApplication, Request $request)
    {
        $form = $this->container->get(FormFactoryInterface::class)->create(FeatureType::class);

        if ($form->handleRequest($request) && $form->isSubmitted() && $form->isValid()) {
            $features = array_column($authorizedApplication['features'], '@id');
            $features[] = $form->get('feature')->getData();
            $this->container->get(Client::class)->save($authorizedApplication->getIri(), [
                '@id' => $authorizedApplication->getIri(),
                'features' => $features,
            ]);

            return $this->redirectToRoute('mis_authorized_application_show', ['id' => $authorizedApplication->getIriId()]);
        }

        return [
            'form' => $form->createView(),
            'authorizedApplication' => $authorizedApplication,
        ];
    }

    #[Route(path: '/{id}/features/{featureId}/remove', name: 'mis_authorized_application_remove_feature', methods: ['GET'])]
    public function removeFeature(
        Request $request,
        #[ApiValueResolverAttribute] ApiData $authorizedApplication,
        $featureId): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('remove_authorized_application_feature', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('mis_authorized_application_show', ['id' => $authorizedApplication->getIriId()]);
        }

        $save = false;
        $features = [];
        foreach ($authorizedApplication['features'] as $feature) {
            if ($featureId === Iri::id($feature['@id'])) {
                $save = true;
                continue;
            }
            $features[] = $feature['@id'];
        }

        if ($save) {
            $this->container->get(Client::class)->save($authorizedApplication->getIri(), [
                '@id' => $authorizedApplication->getIri(),
                'features' => $features,
            ]);
        }

        return $this->redirectToRoute('mis_authorized_application_show', ['id' => $authorizedApplication->getIriId()]);
    }

    #[Route(path: '/{id}/switch', name: 'mis_authorized_application_switch', methods: ['GET'])]
    public function switch(#[ApiValueResolverAttribute] ApiData $authorizedApplication): RedirectResponse
    {
        $this->container->get(Client::class)->save($authorizedApplication->getIri(), [
            '@id' => $authorizedApplication->getIri(),
            'disabled' => !$authorizedApplication['disabled'],
        ]);

        return $this->redirectToRoute('mis_authorized_application_list');
    }

    #[Route(path: '/{id}/delete', name: 'mis_authorized_application_delete', methods: ['GET'])]
    public function delete(Request $request, #[ApiValueResolverAttribute] ApiData $authorizedApplication): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_authorized_application', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('security.error.csrf', [], 'messages'));

            return $this->redirectToRoute('mis_authorized_application_list', ['id' => $authorizedApplication->getIriId()]);
        }

        $this->container->get(Client::class)->remove(self::RESOURCE_URL, $authorizedApplication->getIriId());

        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.authorized_applications.messages.delete_success', [], 'mis'));

        return $this->redirectToRoute('mis_authorized_application_list');
    }
}
