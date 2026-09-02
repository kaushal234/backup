<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use ApiBundle\Request\RequestParameterBagFilter;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Directory\PeopleListDataTableType;
use AppBundle\Filters\Type\Directory\DirectoryPeopleDownloadFilterType;
use AppBundle\Filters\Type\Directory\PeoplePremiseFilterType;
use AppBundle\Filters\Type\Directory\UserActivityFilterType;
use AppBundle\Form\Type\Account\AccountPasswordType;
use AppBundle\Form\Type\AclImportChoiceType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\AddPeopleType;
use AppBundle\Form\Type\Directory\People\DuplicatePeopleType;
use AppBundle\Form\Type\Directory\People\EditPeopleType;
use AppBundle\Form\Type\Directory\People\PeopleAddAclType;
use AppBundle\Form\Type\Directory\People\SearchIdType;
use AppBundle\Form\Type\Directory\People\SearchType;
use AppBundle\Form\Type\Sales\CustomerRelationshipTeam\CustomerRelationshipTeamTransferPartsRep;
use AppBundle\Form\Type\Sales\CustomerRelationshipTeam\CustomerRelationshipTeamTransferSalesRep;
use AppBundle\Form\Type\Sales\CustomerRelationshipTeam\CustomerRelationshipTeamTransferServiceRep;
use AppBundle\Manager\Directory\TeamMemberManager;
use AppBundle\Manager\FileManager;
use AppBundle\Manager\UserManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Sabre\VObject\Component\VCard;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(defaults: ['alvest_module' => 'USER', 'breadcrumb_label' => 'menu.people.title', 'moduleDomain' => 'directory_people'])]
class PeopleController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    final public const searchItemsPerPage = 12;
    final public const RESOURCE_URL = 'people';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            CsvStreamedResponseFactory::class,
            DenormalizerInterface::class,
            FileManager::class,
            FormFactoryInterface::class,
            TranslatorInterface::class,
            UserManager::class,
            ViolationMapper::class,
            RequestParameterBagFilter::class,
            TeamMemberManager::class,
        ]);
    }

    #[Route(path: '/directory/people/{id}/show', name: 'directory_people_show', methods: 'GET', requirements: ['id' => '\d+'], options: ['expose' => true])]
    #[Template('directory/people/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        return [
            'people' => $people,
            'username_error' => $people['username'] !== $people['email'],
        ];
    }

    #[Route(path: '/directory/people/{id}/organization', name: 'directory_people_organization', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/people/organization.html.twig')]
    public function organization(#[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        $users = $this->container->get(Client::class)->get('/people/team_members', ['query' => ['user' => $people['id']]]);

        return [
            'people' => $people,
            'mainUser' => $this->container->get(TeamMemberManager::class)->createTree($users, $people['id']),
        ];
    }

    #[Route(path: '/directory/people/search/byname', name: 'directory_people_search', methods: 'GET|POST', defaults: ['label' => 'menu.people.search', 'domain' => 'messages'])]
    #[Template('directory/people/search.html.twig')]
    public function search(Request $request)
    {
        $form = $this->createForm(SearchType::class, [], [
            'method' => 'GET',
            'csrf_protection' => false,
        ]);

        $page = $request->query->getInt('page', 1);
        $people = null;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $parameters = [
                'q' => str_replace(',', ' ', (string) $form->get('name')->getData()),
                'itemsPerPage' => self::searchItemsPerPage,
                'normalization_groups' => ['group_member'],
            ];

            if (!\array_key_exists('hidden', $form->getData()) || !$form->get('hidden')->getData()) {
                $parameters['hidden'] = 0;
            }

            if (1 !== $page) {
                $parameters['page'] = $page;
            }

            $people = $this->container->get(Client::class)->findBy('people', $parameters);
        }

        return [
            'form' => $form->createView(),
            'peoples' => $people,
            'paginationUrl' => [
                'route' => $request->attributes->get('_route'),
                'parameters' => $request->query->all(),
            ],
        ];
    }

    #[Route(path: '/directory/people/{id}/team-members', name: 'directory_people_team_members', methods: 'GET|POST')]
    #[Template('directory/people/subordinates.html.twig')]
    public function teamMembers(#[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        $subordinates = $this->container->get(Client::class)->findBy(
            'people',
            [
                'supervisor' => $people->getIriId(),
                'hidden' => 0,
            ],
            ['lastname' => 'asc']);

        return [
            'people' => $people,
            'subordinates' => $subordinates,
        ];
    }

    #[Route(path: '/directory/people/search/byname/download', name: 'directory_people_search_download', methods: 'GET')]
    public function searchDownload(Request $request)
    {
        $form = $this->createForm(SearchType::class, [], [
            'action' => $this->generateUrl('directory_people_search'),
            'method' => 'GET',
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->redirectToRoute('directory_people_search', $request->query->all());
        }

        $parameters = [
            'normalization_groups_override' => ['expose_legacy', 'people:export'],
            'q' => $form->get('name')->getData(),
        ];

        if (!$form->get('hidden')->getData()) {
            $parameters['hidden'] = 0;
        }

        return $this->container->get(CsvStreamedResponseFactory::class)->create('people/download_directory', $parameters, 'data.csv');
    }

    #[Route(path: '/directory/people/search/byid', name: 'directory_people_search_id', methods: 'GET', defaults: ['label' => 'menu.people.search_by_id', 'domain' => 'messages'])]
    #[Template('directory/people/search_by_id.html.twig')]
    public function searchId(Request $request)
    {
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $formId = $formFactory->createNamed(
            'people_by_legacy_id',
            SearchIdType::class,
            null,
            ['translation_domain' => 'messages', 'id_label' => 'fields.legacy_id', 'method' => Request::METHOD_GET, 'csrf_protection' => false]
        );
        $formErpEmployeeId = $formFactory->createNamed(
            'people_by_erp_id',
            SearchIdType::class,
            null,
            ['translation_domain' => 'directory', 'id_label' => 'directory.people.fields.erpIdentifier', 'method' => Request::METHOD_GET, 'csrf_protection' => false]
        );
        $searchDone = false;

        foreach (['legacyId' => $formId, 'erpIdentifier' => $formErpEmployeeId] as $property => $form) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $data = $form->getData();
                    $user = $this->container->get(Client::class)->findOneBy('people', [
                        $property => $data['id'],
                        'hidden' => 0,
                        'disabled' => 0,
                    ]);

                    return $this->redirectToRoute('directory_people_show', ['id' => Iri::id($user['@id'])]);
                } catch (\Exception $e) {
                    $searchDone = true;
                }
            }
        }

        return [
            'formId' => $formId->createView(),
            'formErpEmployeeId' => $formErpEmployeeId->createView(),
            'searchDone' => $searchDone,
        ];
    }

    #[Route(path: '/directory/people/{id}/edit', name: 'directory_people_edit', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/people/edit.html.twig')]
    #[IsGranted(attribute: 'FEATURE_PEOPLE_UPDATE_VOTER', subject: new Expression('args["people"].getIri()'))]
    public function edit(Request $request, $id, #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getIriId() === $people->getIri()) {
            return $this->redirectToRoute('account_edit');
        }

        $client = $this->container->get(Client::class);
        $authorizedFields = $client->get('/fields', ['query' => ['iri' => $people['@id'], 'method' => 'PUT']]);
        $authorizedFields[] = 'image';
        $formFactory = $this->container->get(FormFactoryInterface::class);
        $updatedPeople = clone $people;
        $form = $formFactory->createNamed('people',
            EditPeopleType::class,
            $updatedPeople,
            [
                'authorizedFields' => $authorizedFields,
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $peopleData = $form->getData();
                $imageData = $form->get('image')->getData();
                $peopleData['premise'] = '' === $peopleData['premise'] ? null : $peopleData['premise'];

                $people = $this->container->get(UserManager::class)->updatePeople($peopleData);
                $fileId = $people['photo']['id'] ?? null;
                $this->container->get(FileManager::class)->updateImage(
                    $people,
                    'people',
                    $imageData,
                    'photo',
                    $fileId
                );

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('directory.people.messages.success.edit', [], 'directory')
                );

                return $this->redirectToRoute('directory_people_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'people' => $people,
            'form' => $form->createView(),
            'classifications' => $client->findBy(PositionClassificationController::RESOURCE_URL),
            'username_error' => $people['username'] !== $people['email'],
        ];
    }

    #[Route(path: '/directory/people/add', name: 'directory_people_add', methods: 'GET|POST')]
    #[Template('directory/people/add.html.twig')]
    public function add(Request $request)
    {
        $form = $this->container->get(FormFactoryInterface::class)->createNamed('people', AddPeopleType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $peopleData = $form->getData();
            $peopleData['premise'] = '' === $peopleData['premise'] ? null : $peopleData['premise'];

            try {
                $people = $this->container->get(UserManager::class)->addPeople($peopleData);
            } catch (ClientException $e) {
                $people = null;
                $violations = $this->container->get(ViolationMapper::class)->getViolations($e);
                if (\array_key_exists('username', $violations) || \array_key_exists('email', $violations)) {
                    $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('directory.people.messages.error.email_field_error', [], 'directory'));
                } else {
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                }
            }

            $imageData = $form->get('image')->getData();
            if (null !== $people && null !== $imageData['file']) {
                try {
                    $this->container->get(FileManager::class)->uploadFile(
                        $people,
                        $imageData['file'],
                        'people',
                        null,
                        'photo',
                        false,
                        true);
                } catch (ClientException $e) {
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                    $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('directory.people.messages.error.image_field_error', [], 'directory'));
                }
            }

            if (null !== $people) {
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('directory.people.messages.success.add', ['%email%' => $people['email']], 'directory')
                );

                return $this->redirectToRoute(
                    'legacy_calendar',
                    [
                        'm' => ['seq', 'new', 'hr.user.add'],
                        'id' => $people['legacyId'],
                    ]
                );
            }
        }

        return [
            'form' => $form->createView(),
            'classifications' => $this->container->get(Client::class)->findBy(PositionClassificationController::RESOURCE_URL),
        ];
    }

    #[Route(path: '/directory/people/{id}/duplicate', name: 'directory_people_duplicate', methods: 'GET|POST', requirements: ['id' => '\d+'])]
    #[Template('directory/people/duplicate.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function duplicate(Request $request, $id)
    {
        $sourcePeople = $this->container->get(Client::class)->find('people', $id);

        $personalFields = ['@id', 'username', 'email', 'firstname', 'lastname', 'photo', 'legacyId', 'windowsLogin', 'erpIdentifier', 'erpLogin', 'phones'];
        $peopleTarget = array_diff_key($sourcePeople->toArray(), array_fill_keys($personalFields, 1));

        $form = $this->container->get(FormFactoryInterface::class)->createNamed('people', DuplicatePeopleType::class, $peopleTarget, [
            'source' => $sourcePeople,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $peopleData = $form->getData();
                $imageData = $form->get('image')->getData();
                $aclData = $form->get('acls')->getData();

                $userManager = $this->container->get(UserManager::class);
                $people = $userManager->addPeople($peopleData);

                if (null !== $imageData['file']) {
                    $fileManager = $this->container->get(FileManager::class);
                    $fileManager->uploadFile(
                        $people,
                        $imageData['file'],
                        'people',
                        null,
                        'photo',
                        false,
                        true
                    );
                }

                if (!empty($aclData['acls'])) {
                    $userManager->updateAcls($people, $aclData);
                }

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('directory.people.messages.success.duplicate', [], 'directory')
                );

                return $this->redirectToRoute(
                    'legacy_calendar',
                    [
                        'm' => ['seq', 'new', 'hr.user.add'],
                        'id' => $people['legacyId'],
                    ]
                );
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'people' => $sourcePeople,
            'source_id' => $id,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/directory/people/{id}/acls', name: 'directory_people_acls', methods: 'GET', defaults: ['label' => 'menu.people.groups', 'domain' => 'messages'])]
    #[Template('directory/people/index_acls.html.twig')]
    #[IsGranted(
        attribute: new Expression("is_granted('FEATURE_PEOPLE_ACL_VOTER', subject['people'].getIri()) or is_granted('MOO', subject['people'].getIri())"),
        subject: ['people' => new Expression('args["people"]')]
    )]
    public function indexAcls(#[ApiValueResolverAttribute(parameters: ['resource' => 'people', 'filters' => ['normalization_groups' => ['group_member']]])] ApiData $people)
    {
        return [
            'acls' => $people['acls'],
            'userId' => $people->getIriId(),
            'people' => $people,
        ];
    }

    #[Route(path: '/directory/people/{id}/acls/add', name: 'directory_people_add_acl', methods: 'GET|POST')]
    #[Route(path: '/directory/people/{id}/acls/delegate', name: 'directory_people_delegate_acl', defaults: ['label' => 'menu.people.delegate', 'domain' => 'messages'], methods: 'GET|POST')]
    #[Template('directory/people/add_acl.html.twig')]
    #[IsGranted(attribute: 'FEATURE_PEOPLE_ADD_ACL_VOTER', subject: new Expression('args["people"].getIri()'))]
    public function addAcl(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'people', 'filters' => ['normalization_groups' => ['group_member']]])] ApiData $people)
    {
        $route = $request->attributes->get('_route');
        if (('directory_people_add_acl' === $route && !$this->isGranted('ACL_SUPERUSER'))
            || ('directory_people_delegate_acl' === $route && !$this->isGranted('FEATURE_PEOPLE_ADD_ACL_VOTER', $people->getIri()))) {
            throw new AccessDeniedException();
        }
        $formOptions = 'directory_people_add_acl' === $route ? [] : ['groupFilter' => true];

        $form = $this->createForm(PeopleAddAclType::class, [], $formOptions);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $client = $this->container->get(Client::class);
                foreach ($data['group'] as $group) {
                    $payload = [
                        'group' => $group,
                        'location' => $data['location'],
                        'expiredAt' => $data['expiredAt'],
                    ];
                    $client->request('people', $people->getIriId(), 'add_acl', 'POST', [
                        'json' => $payload,
                    ]);
                }

                $this->addFlash('success', 'Group added');

                return $this->redirectToRoute($route, ['id' => $people->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        $parameters = [
            'form' => $form->createView(),
            'acls' => $people['acls'],
            'userId' => $people->getIriId(),
            'people' => $people,
        ];

        switch ($route) {
            case 'directory_people_add_acl':
                return $this->render('directory/people/add_acl.html.twig', $parameters);
            default:
                return $this->render('directory/people/delegate_acl.html.twig', $parameters);
        }
    }

    #[Route(path: '/directory/people/{id}/acls/{acl}/remove', name: 'directory_people_remove_acl', methods: 'GET')]
    #[IsGranted(attribute: 'FEATURE_PEOPLE_ACL_VOTER', subject: new Expression('args["acl"].getIri()'))]
    public function removeAcl(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people, #[ApiValueResolverAttribute(parameters: ['id' => 'acl'])] ApiData $acl): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_acl', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete acl: please refresh your form.');

            return $this->redirectToRoute('directory_people_acls', ['id' => $people->getIriId(), 'acl' => $acl->getIriId()]);
        }

        try {
            $this->container->get(Client::class)->remove('acls', $acl->getIriId());
            $this->addFlash('success', 'Acl deleted successfully');
        } catch (ClientException $e) {
            $this->addFlash('error', 'Cannot delete acl: '.$e->getMessage());
        }

        return $this->redirectToRoute('directory_people_acls', ['id' => $people->getIriId()]);
    }

    #[Route(path: '/directory/people/{id}/acls/import', name: 'directory_people_import_acl', methods: 'GET|POST')]
    #[Template('directory/people/import_acl.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function importAcl(Request $request, #[ApiValueResolverAttribute(parameters: ['filters' => ['resource' => 'people', 'normalization_groups' => ['group_member']]])] ApiData $people)
    {
        $formTitle = $this->container->get(TranslatorInterface::class)->trans('directory.import_acl.subtitle', [], 'directory');

        $form = $this->container->get(FormFactoryInterface::class)->createNamed(
            'import_acl',
            AclImportChoiceType::class,
            ['location' => null !== $people['businessUnit'] && null !== $people['businessUnit']['location'] ? $people['businessUnit']['location']['@id'] : null],
            ['csrf_protection' => false]
        );

        $form->handleRequest($request);
        $data = $form->getData();
        if (
            $form->isSubmitted()
            && $form->isValid()
            && $form->has('submit_step_2')
            && (null !== $finalSubmit = $form->get('submit_step_2'))
            && $finalSubmit instanceof ClickableInterface
            && $finalSubmit->isClicked()
        ) {
            $finalData = [];
            $location = null;
            $client = $this->container->get(Client::class);

            if (isset($data['keep_location']) && false === $data['keep_location']) {
                $location = empty($data['location']) ? $client->get($people['businessUnit']['location']['@id']) : $client->get($data['location']);
            }

            if (null !== $location) {
                $finalData['location'] = $location['@id'];
            }

            if (!empty($data['acl_choice']['acls'])) {
                $finalData['acls'] = $data['acl_choice']['acls'];
            }

            try {
                $client->request('people', $people->getIriId(), 'import_acl', 'POST', [
                    'json' => $finalData,
                ]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }

            $this->addFlash('success', 'ACLs imported');

            return $this->redirectToRoute('directory_people_acls', ['id' => $people->getIriId()]);
        }

        return [
            'form' => $form->createView(),
            'acls' => $people['acls'],
            'userId' => $people->getIriId(),
            'people' => $people,
            'form_step_title' => $formTitle,
        ];
    }

    #[Route(path: '/directory/people/{id}/define-password', name: 'directory_people_define_password', methods: 'GET|POST')]
    #[Template('directory/people/define_password.html.twig')]
    #[IsGranted(attribute: 'PEOPLE_PARTIAL_UPDATE_VOTER', subject: new Expression('args["people"].getIri()'))]
    public function definePassword(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        $form = $this->createForm(AccountPasswordType::class, [
            '@id' => $people['@id'],
            '@type' => $people['@type'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(UserManager::class)->updatePeople($form->getData());
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('directory.people.messages.success.define-password', [], 'directory')
                );

                return $this->redirectToRoute('directory_people_show', ['id' => Iri::id($people)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form, ['clearPassword' => 'password.first']);
            }
        }

        return [
            'form' => $form->createView(),
            'people' => $people,
        ];
    }

    #[Route(path: '/directory/people/{id}/vcard', name: 'directory_people_vcard', methods: 'GET')]
    public function vCard(#[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        $peopleVCardNormalizer = $this->container->get(DenormalizerInterface::class);

        /** @var VCard $vCard */
        $vCard = $peopleVCardNormalizer->denormalize($people, VCard::class);
        $filename = filter_var("vCard_{$people['lastname']}-{$people['firstname']}.vcf", \FILTER_SANITIZE_ENCODED);

        return new Response(
            $vCard->serialize(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/x-vcard;charset=UTF-8',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]
        );
    }

    #[Route(path: '/directory/people/download', name: 'directory_people_download', methods: 'GET|POST')]
    #[Template('directory/people/download.html.twig')]
    public function downloadDirectory(Request $request)
    {
        $csvStreamedResponseFactory = $this->container->get(CsvStreamedResponseFactory::class);
        $date = new \DateTime();

        $this->denyAccessUnlessGranted('FEATURE_DOWNLOAD_DIRECTORY');

        $form = $this->createForm(DirectoryPeopleDownloadFilterType::class, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $parameters = ['normalization_groups_override' => ['expose_legacy', 'people:export']];
            $filters = $form->getData();
            if ($filters['hidden']) {
                unset($filters['hidden']);
            }
            $parameters = array_merge($filters, $parameters);

            return $csvStreamedResponseFactory->create('people/download_directory', $parameters, \sprintf('Directory_users_%s.csv', $date->format('Y-m-d')));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/directory/people/{id}/logs', name: 'directory_people_logs', methods: 'GET', defaults: ['label' => 'menu.people.logs', 'domain' => 'messages'])]
    #[Template('directory/people/logs.html.twig')]
    public function log(#[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        return ['people' => $people];
    }

    #[Route(path: '/directory/people/{id}/activity', name: 'directory_people_activity', methods: ['GET', 'POST'], defaults: ['label' => 'directory.people.activity.button', 'domain' => 'directory'])]
    #[Template('directory/people/activity.html.twig')]
    #[IsGranted('ACL_SUPERUSER')]
    public function showActivity(
        #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people,
        Request $request,
    ) {
        $startDate = (new \DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $endDate = new \DateTime();

        $queryParameters = [
            'user' => $people->getIri(),
            'createdAt' => [
                'after' => $startDate->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'before' => $endDate->format(DatePickerType::DEFAULT_INPUT_FORMAT),
            ],
            'order[createdAt]' => 'desc',
        ];

        $form = $this->container->get(FormFactoryInterface::class)->create(UserActivityFilterType::class, $queryParameters, [
            'action' => $this->generateUrl('directory_people_activity', ['id' => $people->getIriId()]),
        ]);

        $form->handleRequest($request);

        $activities = $this->container->get(Client::class)->get('activities', ['query' => $form->getData()])['hydra:member'];

        foreach ($activities as $key => $activity) {
            if (str_starts_with($activity['resource'], '/comments')) {
                unset($activities[$key]);
            }
        }

        return [
            'form' => $form->createView(),
            'people' => $people,
            'activities' => $activities,
        ];
    }

    #[Route(path: '/people/{id}/transfer', name: 'directory_people_transfer_crt', methods: ['GET|POST'])]
    #[Template('directory/people/transfer_crt.html.twig')]
    #[IsGranted('FEATURE_PEOPLE_UPDATE')]
    public function transferCrt(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        $client = $this->container->get(Client::class);

        $activeForms = [
            'salesRepCRT' => $client->findBy('sales/customer_relationship_teams', ['salesRepresentative' => $people['@id']])->count() > 0,
            'serviceRepCRT' => $client->findBy('sales/customer_relationship_teams', ['serviceRepresentative' => $people['@id']])->count() > 0,
            'partsRepCRT' => $client->findBy('sales/customer_relationship_teams', ['partsRepresentative' => $people['@id']])->count() > 0,
        ];

        $forms = [
            'salesRepTarget' => CustomerRelationshipTeamTransferSalesRep::class,
            'partsRepTarget' => CustomerRelationshipTeamTransferPartsRep::class,
            'serviceRepTarget' => CustomerRelationshipTeamTransferServiceRep::class,
        ];

        $formName = [];

        foreach ($forms as $targetField => $formClassName) {
            $form = $this->container->get(FormFactoryInterface::class)->createNamed(
                $targetField,
                $formClassName
            );
            $formName[$targetField] = $form;

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $target = $client->find('people', Iri::id($data['representative']));
                $payload[$targetField] = $target['@id'];

                try {
                    $client->request('people', $people->getIriId(), 'transfer', 'PUT',
                        [
                            'json' => $payload,
                        ]);

                    $this->addFlash(
                        'success',
                        $this->container->get(TranslatorInterface::class)->trans('customer_relationship_team.messages.success.transfer', [], 'customer_relationship_team')
                    );

                    return $this->redirectToRoute('directory_people_show', ['id' => Iri::id($people)]);
                } catch (ClientException $e) {
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                }
            }
        }

        return [
            'people' => $people,
            'activeForms' => $activeForms,
            'formSalesRep' => $formName['salesRepTarget']->createView(),
            'formPartsRep' => $formName['partsRepTarget']->createView(),
            'formServiceRep' => $formName['serviceRepTarget']->createView(),
        ];
    }

    #[Route(path: '/directory/people/list', name: 'directory_people_home', methods: ['GET', 'POST'])]
    #[Template('/directory/people/list.html.twig')]
    public function listAll(Request $request)
    {
        $datatable = $this->createDataTable(
            PeopleListDataTableType::class,
            PeopleListDataTableType::RESOURCE.'?hidden=0&disabled=0&normalization_groups[]=premise'
        );
        $datatable->handleRequest($request);
        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'peopleListDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/directory/people/{id}/mentoring', name: 'directory_people_mentoring', methods: 'GET', requirements: ['id' => '\d+'])]
    #[Template('directory/people/mentoring.html.twig')]
    #[IsGranted(attribute: 'PEOPLE_MENTORING_ACCESS_VOTER', subject: new Expression('args["people"].getIri()'))]
    public function showMentoring(#[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people)
    {
        return ['people' => $people];
    }

    #[\Symfony\Component\Routing\Attribute\Route(path: '/directory/people/map', name: 'people_premise_map', methods: ['GET|POST'], defaults: ['label' => 'button.map', 'domain' => 'messages'])]
    #[Template('directory/people/map.html.twig')]
    public function map(Request $request)
    {
        $client = $this->container->get(Client::class);
        $parameters = [
            'hidden' => 0,
            'disabled' => 0,
            'pagination' => false,
            'normalization_groups_override' => ['map_premise_people', 'premise:detail', 'airport_list'],
        ];
        $filtered = false;

        $csvStreamedResponseFactory = $this->container->get(CsvStreamedResponseFactory::class);

        $formFilter = $this->container->get(FormFactoryInterface::class)->createNamed('', PeoplePremiseFilterType::class, [], [
            'method' => Request::METHOD_GET,
            'action' => $this->generateUrl('people_premise_map'),
        ]);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $data = $formFilter->getData();
            $filtered = true;
            $parameters = array_merge($parameters, $data);
            if ($this->isGranted('FEATURE_DOWNLOAD_DIRECTORY')) {
                if ($formFilter->getClickedButton() && 'download' === $formFilter->getClickedButton()->getName()) {
                    $parameters = array_merge($parameters, $data);

                    return $csvStreamedResponseFactory->create('people', $parameters, 'data.csv');
                }
            }
        }

        $normalizedPeople = $client->findBy(self::RESOURCE_URL, $parameters, ['id' => 'desc']);

        return [
            'filtered' => $filtered,
            'mapReportTitle' => $filtered ? 'directory.people.title.filtered_list' : 'directory.people.title.people_list',
            'formFilter' => $formFilter->createView(),
            'peoples' => $normalizedPeople,
        ];
    }
}
