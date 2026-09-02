<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\ServiceArea;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/service-areas', defaults: ['breadcrumb_label' => 'menu.service_areas.title', 'moduleDomain' => 'service_area'])]
#[IsGranted('SERVICE_AREA_WRITE_VOTER')]
class DeleteController extends AbstractController
{
    final public const string DELETE_TOKEN = 'service_area_delete';

    public function __construct(
        private readonly Client $client,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/{id}/delete', name: 'service_areas_delete', methods: ['GET'])]
    public function __invoke(Request $request, #[ApiValueResolverAttribute] ApiData $serviceArea): RedirectResponse
    {
        if (!$this->isCsrfTokenValid(self::DELETE_TOKEN, $request->query->get('_token'))) {
            $this->addFlash('error', 'missing csrf token');

            return $this->redirectToRoute('service_area_home');
        }

        try {
            $this->client->remove(IndexController::RESOURCE, $serviceArea->getIriId());
            $this->addFlash('success', $this->translator->trans('service_area.delete_success', ['%name%' => $serviceArea['name']], 'service'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->translator->trans('errors.something_went_wrong', [], 'messages'));
        }

        return $this->redirectToRoute('service_area_home');
    }
}
