<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Sales\CustomerRelationshipTeamFilterType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\LegacyIdSearchType;
use AppBundle\Form\Type\SimpleSearchType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/customer-relationship-teams', defaults: ['alvest_module' => 'CRT', 'moduleDomain' => 'customer_relationship_team'])]
class CustomerRelationshipTeamController extends AbstractController
{
    final public const searchItemsPerPage = 25;
    final public const RESOURCE_URL = 'sales/customer_relationship_teams';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class]);
    }

    #[Route(name: 'customer_relationship_team_home', methods: ['GET|POST'])]
    #[Template('sales/customer_relationship_teams/list.html.twig')]
    public function list(Request $request)
    {
        // Search action
        $simpleSearchForm = $this->createForm(SimpleSearchType::class, [], [
            'method' => 'GET',
            'csrf_protection' => false,
            'search_label' => false,
            'search_placeholder' => 'Search for...',
        ]);

        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $legacyIdSearchForm = $this->createForm(LegacyIdSearchType::class, null, [
            'legacy_id_label' => false,
            'legacy_id_placeholder' => 'By Legacy ID',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $this->container->get(Client::class)->get(\sprintf('%s/%s', self::RESOURCE_URL, $id));

                return $this->redirectToRoute('customer_relationship_team_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('CRT #%s does not exist', $id));
            }
        }

        $legacyIdSearchForm->handleRequest($request);
        if ($legacyIdSearchForm->isSubmitted() && $legacyIdSearchForm->isValid()) {
            $legacyId = $legacyIdSearchForm->get('legacyId')->getData();
            try {
                $crt = $this->container->get(Client::class)->findOneBy(self::RESOURCE_URL, ['legacyId' => $legacyId]);

                return $this->redirectToRoute('customer_relationship_team_show', ['id' => Iri::id($crt)]);
            } catch (\RangeException $e) {
                $this->addFlash('error', \sprintf('CRT #%s does not exist', $legacyId));
            }
        }

        $reportTitle = 'customer_relationship_team.title.last10';
        $searchTable = false;

        $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['id' => 'desc'];
        $parameters['itemsPerPage'] = $request->query->get('itemsPerPage', 10);

        $formFilter = $this->createForm(CustomerRelationshipTeamFilterType::class);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = array_merge($parameters, $formFilter->getData());
            $parameters['itemsPerPage'] = 20000;
            $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['id' => 'asc'];

            $reportTitle = 'customer_relationship_team.title.show';
            $searchTable = true;
        }

        $simpleSearchForm->handleRequest($request);
        if ($simpleSearchForm->isSubmitted() && $simpleSearchForm->isValid()) {
            $reportTitle = 'customer_relationship_team.title.show';
            $searchTable = true;
            $parameters = [
                'q' => $simpleSearchForm->get('q')->getData(),
                'itemsPerPage' => self::searchItemsPerPage,
            ];
        }

        try {
            $customer_relationship_teams = $this->container->get(Client::class)->findBy(self::RESOURCE_URL, $parameters);
        } catch (ClientException $e) {
            $customer_relationship_teams = [];
        }

        return [
            'customer_relationship_teams' => $customer_relationship_teams,
            'itemsPerPage' => $parameters['itemsPerPage'],
            'formFilter' => $formFilter->createView(),
            'search_form' => $simpleSearchForm->createView(),
            'idSearchForm' => $idSearchForm->createView(),
            'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
            'report_title' => $reportTitle,
            'search_table' => $searchTable,
        ];
    }

    #[Route(path: '/{id}/show', name: 'customer_relationship_team_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[Template('sales/customer_relationship_teams/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $customerRelationshipTeam)
    {
        $extranetUsersLinked = $this->container->get(Client::class)->findBy('sales/extranet_users', [
            'extranetUserAcls.crt' => $customerRelationshipTeam['@id'],
        ]);

        return [
            'customerRelationshipTeam' => $customerRelationshipTeam,
            'extranet_users' => $extranetUsersLinked,
        ];
    }

    #[Route(path: '/add', name: 'customer_relationship_team_add')]
    #[Route(path: '/{id}/edit', name: 'customer_relationship_team_edit', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Route(path: '/{id}/duplicate', name: 'sales_crt_duplicate', methods: 'GET', requirements: ['id' => '\d+'], defaults: ['label' => 'menu.duplicate', 'domain' => 'messages'])]
    #[Template('sales/customer_relationship_teams/react.html.twig')]
    #[IsGranted('FEATURE_CUSTOMER_RELATIONSHIP_TEAM_WRITE')]
    public function react(Request $request, $id = null)
    {
        $client = $this->container->get(Client::class);
        $customerRelationshipTeam = null !== $id ? $client->find(self::RESOURCE_URL, $id, ['raw_results' => true]) : [];

        switch ($request->attributes->get('_route')) {
            case 'customer_relationship_team_add':
                $formType = 'creation';
                break;
            case 'customer_relationship_team_edit':
                $formType = 'edition';
                break;
            case 'sales_crt_duplicate':
                $formType = 'duplicate';
                break;
            default:
                $formType = null;
        }

        $props = ['formType' => $formType];

        $peopleFilters = [
            'hidden' => 0,
            'disabled' => 0,
            'pagination' => 0,
            'normalization_groups_override' => ['people_list'],
        ];

        $initialState = [
            'user' => [
                'partsRepresentatives' => $client->findBy('people', array_merge($peopleFilters, ['acls.group.name' => ['ROLE_PARTS', 'GG_PARTS', 'GG_PARTS_AGENTS']]), ['lastname' => 'asc', 'firstname' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
                'serviceRepresentatives' => $client->findBy('people', array_merge($peopleFilters, ['acls.group.name' => ['GG_SERVICE_AGENTS', 'GG_SERVICE']]), ['lastname' => 'asc', 'firstname' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
                'salesRepresentative' => $client->findBy('people', array_merge($peopleFilters, ['acls.group.name' => ['ROLE_ASM', 'ROLE_EVP', 'GG_SALES_AGENTS', 'ROLE_SA', 'GG_SALES', 'GG_SERVICE']]), ['lastname' => 'asc', 'firstname' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
            ],
            'location' => [
                'sparePartsHubs' => $client->findBy('locations', ['capability.sparePartsHub' => 1], ['name' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
                'serviceHubs' => $client->findBy('locations', ['capability.serviceHub' => 1], ['name' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
                'ssos' => $client->findBy('locations', ['capability.sso' => 1], ['name' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
            ],
        ];

        if ('edition' !== $formType) {
            $initialState = array_merge($initialState, [
                'extranetUser' => [
                    'extranetUserGroups' => $client->get('sales/extranet_user_groups', ['query' => ['order' => ['name' => 'asc']]])['hydra:member'] ?? [],
                ],
            ]);
        }

        if (null !== $customerRelationshipTeam) {
            $initialState = array_merge($initialState, [
                'crt' => [
                    'details' => $customerRelationshipTeam,
                ],
            ]);
        }

        return [
            'initialState' => $initialState,
            'props' => $props,
            'customerRelationshipTeam' => $customerRelationshipTeam,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'customer_relationship_team_delete', methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_CUSTOMER_RELATIONSHIP_TEAM_WRITE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $id);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('customer_relationship_team.messages.success.delete', [], 'customer_relationship_team')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('customer_relationship_team.messages.error.delete', [], 'customer_relationship_team')
            );
        }

        return $this->redirectToRoute('customer_relationship_team_home');
    }
}
