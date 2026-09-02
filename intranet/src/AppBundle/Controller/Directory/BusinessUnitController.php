<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitDeleteType;
use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/business-units', defaults: ['alvest_module' => 'DIR', 'breadcrumb_label' => 'menu.business_unit.title', 'moduleDomain' => 'directory_business_units'])]
class BusinessUnitController extends AbstractController
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

    #[Route(path: '', name: 'directory_business_units_home', methods: 'GET')]
    #[Template('directory/business_unit/list.html.twig')]
    public function list()
    {
        return ['business_units' => $this->client->findBy('business_units', [], ['name' => 'asc'])];
    }

    #[Route(path: '/{id}/show', name: 'directory_business_units_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/business_unit/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $businessUnit)
    {
        return [
            'business_unit' => $businessUnit,
            'members' => $this->client->findBy('people', [
                'businessUnit' => $businessUnit->getIri(),
                'hidden' => 0,
                'disabled' => 0,
                'pagination' => 0,
                'normalization_groups_override' => ['people_list'],
            ]),
        ];
    }

    #[Route(path: '/add', name: 'directory_business_units_add', methods: 'GET|POST')]
    #[Template('directory/business_unit/add.html.twig')]
    #[IsGranted('FEATURE_BUSINESS_UNIT_WRITE')]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('business_unit', BusinessUnitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $businessUnit = $this->client->save('business_units', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.business_unit.messages.success.add', [], 'directory')
                );

                return $this->redirectToRoute('directory_business_units_show', ['id' => Iri::id($businessUnit)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'directory_business_units_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/business_unit/edit.html.twig')]
    #[IsGranted('FEATURE_BUSINESS_UNIT_WRITE')]
    public function edit(Request $request, $id)
    {
        $businessUnit = $this->client->find('business_units', $id);

        $form = $this->formFactory->createNamed('business_unit', BusinessUnitType::class, $businessUnit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('business_units', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.business_unit.messages.success.edit', [], 'directory')
                );

                return $this->redirectToRoute('directory_business_units_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'business_unit' => $businessUnit,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'directory_business_units_delete', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/business_unit/delete.html.twig')]
    #[IsGranted('FEATURE_BUSINESS_UNIT_WRITE')]
    public function delete(Request $request, $id)
    {
        $businessUnit = $this->client->find('business_units', $id);
        $users = $this->client->findBy('people', ['businessUnit' => $businessUnit['@id']]);

        $form = $this->formFactory->createNamed('business_unit', BusinessUnitDeleteType::class, $businessUnit, [
            'action' => $this->generateUrl('directory_business_units_delete', ['id' => Iri::id($businessUnit['@id'])]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->remove('business_units', $id);

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.business_unit.messages.success.delete', [], 'directory')
                );

                return $this->redirectToRoute('directory_business_units_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'business_unit' => $businessUnit,
            'used' => $users->count() > 0,
            'form' => $form->createView(),
        ];
    }
}
