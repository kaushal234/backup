<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use AppBundle\Form\Type\Directory\JuridicalLocation\JuridicalLocationDeleteType;
use AppBundle\Form\Type\Directory\JuridicalLocation\JuridicalLocationType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/juridical-locations', defaults: ['alvest_module' => 'DIR', 'breadcrumb_label' => 'menu.juridical_location.title', 'moduleDomain' => 'directory_juridical_locations'])]
#[IsGranted(attribute: new Expression("is_granted('ACL_SUPERUSER') or is_granted('ACL_ROLE_CFO') or is_granted('ACL_ROLE_LM')"))]
class JuridicalLocationController extends AbstractController
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

    #[Route(path: '', name: 'directory_juridical_locations_home', methods: 'GET')]
    #[Template('directory/juridical_location/list.html.twig')]
    public function list()
    {
        return ['juridical_locations' => $this->client->findBy('juridical_locations', [], ['name' => 'asc'])];
    }

    #[Route(path: '/{id}/show', name: 'directory_juridical_locations_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/juridical_location/show.html.twig')]
    public function show($id)
    {
        return ['juridical_location' => $this->client->find('juridical_locations', $id)];
    }

    #[Route(path: '/add', name: 'directory_juridical_locations_add', methods: 'GET|POST')]
    #[Template('directory/juridical_location/add.html.twig')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('juridical_location', JuridicalLocationType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $juridicalLocation = $this->client->save('juridical_locations', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.juridical_location.messages.success.add', [], 'directory')
                );

                return $this->redirectToRoute('directory_juridical_locations_show', ['id' => Iri::id($juridicalLocation)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'directory_juridical_locations_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/juridical_location/edit.html.twig')]
    public function edit(Request $request, $id)
    {
        $juridicalLocation = $this->client->find('juridical_locations', $id);

        $form = $this->formFactory->createNamed('juridical_location', JuridicalLocationType::class, $juridicalLocation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('juridical_locations', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.juridical_location.messages.success.edit', [], 'directory')
                );

                return $this->redirectToRoute('directory_juridical_locations_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'juridical_location' => $juridicalLocation,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'directory_juridical_locations_delete', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/juridical_location/delete.html.twig')]
    public function delete(Request $request, $id)
    {
        $juridicalLocation = $this->client->find('juridical_locations', $id);
        $locations = $this->client->findBy('locations', ['juridicalLocation' => $juridicalLocation['@id']]);

        $form = $this->formFactory->createNamed('juridical_location', JuridicalLocationDeleteType::class, $juridicalLocation, [
            'action' => $this->generateUrl('directory_juridical_locations_delete', ['id' => Iri::id($juridicalLocation['@id'])]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->remove('juridical_locations', $id);

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.juridical_location.messages.success.delete', [], 'directory')
                );

                return $this->redirectToRoute('directory_juridical_locations_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'juridical_location' => $juridicalLocation,
            'used' => $locations->count() > 0,
            'form' => $form->createView(),
        ];
    }
}
