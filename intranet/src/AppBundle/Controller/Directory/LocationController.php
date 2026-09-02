<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\Location\LocationType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/locations', defaults: ['alvest_module' => 'DIR', 'breadcrumb_label' => 'menu.location.title', 'moduleDomain' => 'directory_locations'])]
class LocationController extends AbstractController
{
    private readonly Client $client;

    private readonly FormFactoryInterface $formFactory;

    private readonly ViolationMapper $violationMapper;

    private readonly TranslatorInterface $translator;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
    }

    #[Route(path: '', name: 'directory_locations_home', methods: 'GET')]
    #[Template('directory/location/list.html.twig')]
    public function list(Request $request)
    {
        $showDisabled = (bool) $request->query->get('showDisabled', false);

        return [
            'locations' => $this->client->findBy('locations', $showDisabled ? [] : ['state.disabled' => false], ['name' => 'asc']),
            'showDisabled' => $showDisabled,
        ];
    }

    #[Route(path: '/{id}/show', name: 'directory_locations_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/location/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $location)
    {
        return ['location' => $location];
    }

    #[Route(path: '/add', name: 'directory_locations_add', methods: 'GET|POST')]
    #[Template('directory/location/add.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('location', LocationType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $location = $this->client->save('locations', $form->getData());
                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.location.messages.success.add', [], 'directory')
                );

                return $this->redirectToRoute('directory_locations_home', ['id' => Iri::id($location)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return ['form' => $form->createView()];
    }

    #[Route(path: '/{id}/edit', name: 'directory_locations_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/location/edit.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function edit(Request $request, #[ApiValueResolverAttribute] ApiData $location)
    {
        $form = $this->formFactory->createNamed('location', LocationType::class, $location);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('locations', $form->getData());
                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.location.messages.success.edit', [], 'directory')
                );

                return $this->redirectToRoute('directory_locations_show', ['id' => $location->getIriId()]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'location' => $location,
            'form' => $form->createView(),
        ];
    }
}
