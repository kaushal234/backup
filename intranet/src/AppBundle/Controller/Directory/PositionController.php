<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Directory\PositionMembersDataTableType;
use AppBundle\Form\Type\Directory\Position\PositionDeleteType;
use AppBundle\Form\Type\Directory\Position\PositionType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/directory/positions', defaults: ['alvest_module' => 'DIR', 'breadcrumb_label' => 'menu.directory_position.title', 'moduleDomain' => 'directory_positions'])]
class PositionController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

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

    #[Route(path: '', name: 'directory_positions_home', methods: 'GET')]
    #[Template('directory/position/list.html.twig')]
    public function list()
    {
        return ['positions' => $this->client->findBy('positions', [], ['description' => 'asc'])];
    }

    #[Route(path: '/{id}/show', name: 'directory_positions_show', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/position/show.html.twig')]
    public function show($id)
    {
        return ['position' => $this->client->find('positions', $id)];
    }

    #[Route(path: '/add', name: 'directory_positions_add', methods: 'GET|POST')]
    #[Template('directory/position/add.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('ACL_SUPERUSER') or is_granted('ACL_GG_HR')"))]
    public function add(Request $request)
    {
        $form = $this->formFactory->createNamed('position', PositionType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $position = $this->client->save('positions', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.position.messages.success.add', [], 'directory')
                );

                return $this->redirectToRoute('directory_positions_show', ['id' => Iri::id($position)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'directory_positions_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/position/edit.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('ACL_SUPERUSER') or is_granted('ACL_GG_HR')"))]
    public function edit(Request $request, $id)
    {
        $position = $this->client->find('positions', $id);

        $form = $this->formFactory->createNamed('position', PositionType::class, $position);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('positions', $form->getData());

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.position.messages.success.edit', [], 'directory')
                );

                return $this->redirectToRoute('directory_positions_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'position' => $position,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'directory_positions_delete', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/position/delete.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function delete(Request $request, $id)
    {
        $position = $this->client->find('positions', $id);
        $users = $this->client->findBy('people', ['position' => $position['@id']]);

        $form = $this->formFactory->createNamed('position', PositionDeleteType::class, $position, [
            'action' => $this->generateUrl('directory_positions_delete', ['id' => Iri::id($position['@id'])]),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->remove('positions', $id);

                $this->addFlash(
                    'success',
                    $this->translator->trans('directory.position.messages.success.delete', [], 'directory')
                );

                return $this->redirectToRoute('directory_positions_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'usersCount' => \count($users),
            'position' => $position,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/people', name: 'directory_positions_people', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/position/people.html.twig')]
    public function people(#[ApiValueResolverAttribute] ApiData $position, Request $request): array|Response
    {
        $resource = \sprintf(
            '%s?position=%s&disabled=0&hidden=0',
            PositionMembersDataTableType::RESOURCE,
            $position['@id'],
        );
        $dataTable = $this->createDataTable(
            PositionMembersDataTableType::class,
            $resource,
            [
                'title' => \sprintf(
                    '%s %s',
                    ucfirst($this->translator->trans('mis.groups.members_of', [], 'mis')),
                    $position['code'],
                ),
            ],
        );
        $dataTable->handleRequest($request);
        if ($dataTable->isExporting() && $dataTable->getQuery() instanceof ApiProxyQuery) {
            return $dataTable->getQuery()->export();
        }
        if ($dataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($dataTable);
        }

        return [
            'position' => $position,
            'membersDataTable' => $dataTable->createView(),
        ];
    }
}
