<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Directory\Region\RegionDeleteType;
use AppBundle\Form\Type\Directory\Region\RegionType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/regions', defaults: ['alvest_module' => 'DIR', 'breadcrumb_label' => 'menu.region.title', 'moduleDomain' => 'directory_regions'])]
class RegionController extends AbstractController
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

    #[Route(path: '', name: 'directory_regions_home', methods: 'GET')]
    #[Template('directory/region/list.html.twig')]
    public function list()
    {
        return ['regions' => $this->client->findBy('regions', [], ['name' => 'asc'])];
    }

    #[Route(path: '/{id}/show', name: 'directory_regions_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/region/show.html.twig')]
    public function show(#[ApiValueResolverAttribute] ApiData $region)
    {
        return [
            'region' => $region,
            'members' => $this->client->findBy('people', [
                'businessUnit.region' => $region->getIri(),
                'hidden' => 0,
                'disabled' => 0,
                'pagination' => 0,
                'normalization_groups_override' => ['people_list'],
            ]),
        ];
    }

    #[Route(path: '/add', name: 'directory_regions_add', methods: 'GET|POST')]
    #[Template('directory/region/add.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"))]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('region', RegionType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $region = $this->client->save('regions', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.region.messages.success.add', [], 'directory')
                );

                return $this->redirectToRoute('directory_regions_show', ['id' => Iri::id($region)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'directory_regions_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/region/edit.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"))]
    public function edit(Request $request, $id)
    {
        $region = $this->client->find('regions', $id);

        $form = $this->formFactory->createNamed('region', RegionType::class, $region);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('regions', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.region.messages.success.edit', [], 'directory')
                );

                return $this->redirectToRoute('directory_regions_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'region' => $region,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'directory_regions_delete', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/region/delete.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DIVISION_WRITE') or is_granted('MOO_DIR')"))]
    public function delete(Request $request, $id)
    {
        $region = $this->client->find('regions', $id);
        $businessUnits = $this->client->findBy('business_units', ['region' => $region['@id']]);

        $form = $this->formFactory->createNamed('region', RegionDeleteType::class, $region, [
            'action' => $this->generateUrl('directory_regions_delete', ['id' => Iri::id($region['@id'])]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->remove('regions', $id);

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.region.messages.success.delete', [], 'directory')
                );

                return $this->redirectToRoute('directory_regions_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'region' => $region,
            'used' => $businessUnits->count() > 0,
            'form' => $form->createView(),
        ];
    }
}
