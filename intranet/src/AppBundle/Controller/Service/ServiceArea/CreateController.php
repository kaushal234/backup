<?php

declare(strict_types=1);

namespace AppBundle\Controller\Service\ServiceArea;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Form\Type\Service\ServiceAreaType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/service-areas', defaults: ['breadcrumb_label' => 'menu.service_areas.title', 'moduleDomain' => 'service_area'])]
#[IsGranted('SERVICE_AREA_WRITE_VOTER')]
class CreateController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly ViolationMapper $violationMapper,
        private readonly TranslatorInterface $translator,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    #[Route(path: '/add', name: 'service_area_add', methods: ['GET', 'POST'])]
    #[Template('service/service_area/add.html.twig')]
    public function __invoke(Request $request): array|RedirectResponse
    {
        $form = $this->formFactory->create(ServiceAreaType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['airports'] = $request->request->all('service_area')['airports'] ?? [];

            try {
                $this->client->save(IndexController::RESOURCE, $data);
                $this->addFlash('success', $this->translator->trans('service_area.create_success', [], 'service'));

                return $this->redirectToRoute('service_area_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
