<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Mis\GroupType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Group Controller.
 */
#[Route(path: '/mis/groups', defaults: ['alvest_module' => 'MIS', 'breadcrumb_label' => 'menu.groups.title', 'moduleDomain' => 'mis_groups'])]
class GroupController extends AbstractController
{
    private readonly Client $client;

    private readonly ViolationMapper $violationMapper;

    private readonly CsvStreamedResponseFactory $csvStreamedResponseFactory;

    public function __construct(Client $client, ViolationMapper $violationMapper, CsvStreamedResponseFactory $csvStreamedResponseFactory)
    {
        $this->client = $client;
        $this->violationMapper = $violationMapper;
        $this->csvStreamedResponseFactory = $csvStreamedResponseFactory;
    }

    #[Route(path: '', name: 'mis_groups_home', methods: 'GET')]
    #[Template('mis/groups/list.html.twig')]
    public function index()
    {
        $groups = $this->client->findBy('groups', [], ['name' => 'asc']);

        return [
            'groups' => $groups,
        ];
    }

    #[Route(path: '/{id}/show', name: 'mis_groups_show', methods: 'GET')]
    #[Template('mis/groups/show.html.twig')]
    public function show($id)
    {
        $group = $this->client->find('groups', $id);

        return [
            'group' => $group,
        ];
    }

    #[Route(path: '/add', name: 'mis_groups_add', methods: 'GET|POST')]
    #[Template('mis/groups/add.html.twig')]
    #[IsGranted('FEATURE_GROUPS_ADMIN')]
    public function add(Request $request)
    {
        $form = $this->createForm(GroupType::class, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('groups', $form->getData());

                $this->addFlash('success', 'Group created successfully');

                return $this->redirectToRoute('mis_groups_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'mis_groups_edit', methods: 'GET|POST')]
    #[Template('mis/groups/edit.html.twig')]
    #[IsGranted('FEATURE_GROUPS_ADMIN')]
    public function edit(Request $request, $id)
    {
        $group = $this->client->find('groups', $id);

        $form = $this->createForm(GroupType::class, $group);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->client->save('groups', $form->getData());

                $this->addFlash('success', 'Group updated successfully');

                return $this->redirectToRoute('mis_groups_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'group' => $group,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'mis_groups_delete', methods: 'GET|POST')]
    #[IsGranted('FEATURE_GROUPS_ADMIN')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->client->remove('groups', $id);

            return $this->redirectToRoute('mis_groups_home');
        } catch (ClientException $e) {
            $this->addFlash('error', 'Cannot delete this group');

            return $this->redirectToRoute('mis_groups_show', ['id' => $id]);
        }
    }

    #[Route(path: '/{id}/people', name: 'mis_groups_people', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('mis/groups/people.html.twig')]
    public function people(#[ApiValueResolverAttribute] ApiData $group)
    {
        $people = $this->client->findBy(
            'people',
            [
                'acls.group' => $group['@id'],
                'disabled' => false,
                'hidden' => false,
                'pagination' => 0,
                'normalization_groups' => ['group_member'], ],
            [
                'lastname' => 'ASC',
                'firstname' => 'ASC',
            ]
        );

        foreach ($people as &$person) {
            $locations = [];
            foreach ($person['acls'] as $acl) {
                if ($acl['group']['name'] !== $group['name'] || empty($acl['location']['name'])) {
                    continue;
                }
                $locations[] = $acl['location']['name'];
            }

            $person['aclsLocation'] = implode(' / ', $locations);
        }

        return [
            'group' => $group,
            'people' => $people,
        ];
    }

    #[Route(path: '/{id}/people/download', name: 'mis_groups_people_download', methods: 'GET', requirements: ['id' => '\d+'])]
    #[IsGranted('FEATURE_GROUPS_ADMIN')]
    public function peopleDownload(#[ApiValueResolverAttribute] ApiData $group)
    {
        $parameters = [
            'acls.group' => $group['@id'],
            'hidden' => 0,
            'disabled' => 0,
            'normalization_groups_override' => ['expose_legacy', 'people:export'],
            'order' => [
                'lastname' => 'ASC',
                'firstname' => 'ASC',
            ],
        ];

        return $this->csvStreamedResponseFactory->create('people', $parameters, 'data.csv');
    }
}
