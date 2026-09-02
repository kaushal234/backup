<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\MinutesOfMeeting\MeetingController;
use AppBundle\Controller\Parts\SparePartsRequestController;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Sales\ExtranetUserDataTableType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\LegacyIdSearchType;
use AppBundle\Form\Type\Sales\ExtranetUser\AddExtranetUserAclType;
use AppBundle\Form\Type\Sales\ExtranetUser\ExtranetUserEmailType;
use AppBundle\Form\Type\Sales\ExtranetUser\ExtranetUserType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Sabre\VObject\Component\VCard;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/contacts', defaults: ['alvest_module' => 'XU', 'breadcrumb_label' => 'menu.contacts.title', 'moduleDomain' => 'sales_contact'])]
class ExtranetUserController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    final public const searchItemsPerPage = 25;
    final public const TRANSLATION_DOMAIN = 'contacts';
    final public const RESOURCE_URL = 'sales/extranet_users';
    final public const GROUP_RESOURCE_URL = 'sales/extranet_user_groups';
    final public const ACLS_RESOURCE_URL = 'sales/extranet_user_acls';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            DenormalizerInterface::class,
            TranslatorInterface::class,
            ViolationMapper::class,
        ]);
    }

    #[Route(path: '', name: 'sales_contact_home', methods: ['GET|POST'])]
    #[Template('sales/contacts/list.html.twig')]
    public function list(Request $request)
    {
        $datatable = $this->createDataTable(ExtranetUserDataTableType::class, ExtranetUserDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $legacyIdSearchForm = $this->createForm(LegacyIdSearchType::class, null, [
            'legacy_id_label' => false,
            'legacy_id_placeholder' => 'By Legacy ID',
        ]);

        $client = $this->container->get(Client::class);
        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::RESOURCE_URL, $id));

                return $this->redirectToRoute('sales_contact_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('Extranet User #%s does not exist', $id));
            }
        }

        $legacyIdSearchForm->handleRequest($request);
        if ($legacyIdSearchForm->isSubmitted() && $legacyIdSearchForm->isValid()) {
            $legacyId = $legacyIdSearchForm->get('legacyId')->getData();
            try {
                $customer = $client->findOneBy(self::RESOURCE_URL, ['legacyId' => $legacyId]);

                return $this->redirectToRoute('sales_contact_show', ['id' => Iri::id($customer)]);
            } catch (\RangeException $e) {
                $this->addFlash('error', \sprintf('Extranet User #%s does not exist', $legacyId));
            }
        }

        return [
            'extranetUserDatatable' => $datatable->createView(),
            'idSearchForm' => $idSearchForm->createView(),
            'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'sales_contact_show', methods: ['GET'])]
    #[Template('sales/contacts/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $extranetUser)
    {
        $client = $this->container->get(Client::class);

        return [
            'extranet_user_acls' => $client->findBy(self::ACLS_RESOURCE_URL, ['extranetUser' => $extranetUser['@id']]),
            'extranetUser' => $extranetUser,
            'deliveryAddresses' => $client->findBy(SparePartsRequestController::SPARE_PARTS_REQUESTS_DELIVERY_ADDRESSES_URL, ['contact' => $extranetUser->getIri()]),
            'meetings' => $client->findBy(MeetingController::RESOURCE_URL, ['customerContacts' => $extranetUser->getIri()]),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'sales_contact_edit', methods: ['GET|POST'])]
    #[Template('sales/contacts/edit.html.twig')]
    #[IsGranted('FEATURE_EXTRANET_USER_EDIT')]
    public function edit(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $extranetUser)
    {
        $extranetUserForm = $this->container->get('form.factory')->createNamed('extranet_user_form', ExtranetUserType::class, $extranetUser, ['phones' => $extranetUser['phones']]);

        $extranetUserForm->handleRequest($request);
        if ($extranetUserForm->isSubmitted() && $extranetUserForm->isValid()) {
            try {
                $extranetUserEdited = $extranetUserForm->getData();
                $extranetUserEdited['username'] = $extranetUserEdited['email'];
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $extranetUserEdited);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('contacts.messages.success.edit', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('sales_contact_show', ['id' => $extranetUser->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $extranetUserForm);
            }
        }

        return [
            'form' => $extranetUserForm->createView(),
            'extranetUser' => $extranetUser,
        ];
    }

    #[Route(path: '/{id}/crt_roles', name: 'sales_contact_show_crt_roles', methods: ['GET'])]
    #[Template('sales/contacts/crt_roles.html.twig')]
    public function showCrtRoles(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $extranetUser)
    {
        $extranetUserAcls = $this->container->get(Client::class)->findBy(self::ACLS_RESOURCE_URL, ['extranetUser' => $extranetUser['@id']]);

        return [
            'extranet_user_acls' => $extranetUserAcls,
            'extranetUser' => $extranetUser,
            'add' => false,
        ];
    }

    #[Route(path: '/crt_roles/{id}/delete', name: 'sales_contact_delete_crt_roles', methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_EXTRANET_USER_EDIT')]
    public function deleteCrtRoles(#[ApiValueResolverAttribute(parameters: ['resource' => self::ACLS_RESOURCE_URL])] ApiData $extranetUserAcl, Request $request): RedirectResponse
    {
        $client = $this->container->get(Client::class);
        $extranetUser = $client->find(self::RESOURCE_URL, Iri::id($extranetUserAcl['extranetUser']));

        if (!$this->isCsrfTokenValid('delete_role', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete role: please refresh your page.');
        }

        try {
            $client->remove(self::ACLS_RESOURCE_URL, $extranetUserAcl['id']);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('contacts.acls.messages.success.delete', [], 'contacts')
            );
        } catch (ClientException $e) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('contacts.acls.messages.error.delete', [], self::TRANSLATION_DOMAIN));
        }

        return $this->redirectToRoute('sales_contact_show_crt_roles', ['id' => $extranetUser->getIriId()]);
    }

    #[Route(path: '/{id}/crt_roles/add', name: 'sales_contact_add_crt_roles', methods: ['GET|POST'])]
    #[Template('sales/contacts/crt_roles.html.twig')]
    #[IsGranted('FEATURE_EXTRANET_USER_EDIT')]
    public function addCrtRoles(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $extranetUser, Request $request)
    {
        $client = $this->container->get(Client::class);
        $extranetUserAcls = $client->findBy(self::ACLS_RESOURCE_URL, ['extranetUser' => $extranetUser['@id']]);
        $extranetUserAddAclForm = $this->container->get('form.factory')->createNamed('xu_add_roles', AddExtranetUserAclType::class);

        $extranetUserAddAclForm->handleRequest($request);
        if ($extranetUserAddAclForm->isSubmitted() && $extranetUserAddAclForm->isValid()) {
            $data = $extranetUserAddAclForm->getData();
            if (isset($data['crt'], $data['role']) && \count($data['role']) > 0) {
                try {
                    foreach ($data['role'] as $role) {
                        $client->post(self::ACLS_RESOURCE_URL, [
                            'json' => [
                                'crt' => $data['crt'],
                                'extranetUserGroup' => $role,
                                'cDel' => $data['cDel'] ?? null,
                                'extranetUser' => $extranetUser->getIri(),
                            ],
                        ]);
                    }

                    return $this->redirectToRoute('sales_contact_show_crt_roles', ['id' => $extranetUser['id']]);
                } catch (ClientException $e) {
                    $errorDescription = json_decode($e->getResponse()->getContent(false), true);
                    $this->addFlash('error', $errorDescription['hydra:description']);
                }
            }
        }

        return [
            'form' => $extranetUserAddAclForm->createView(),
            'extranetUser' => $extranetUser,
            'extranet_user_acls' => $extranetUserAcls,
            'add' => true,
        ];
    }

    #[Route(path: '/{id}/confirmation_mail', name: 'sales_contact_email', methods: ['GET|POST'])]
    #[Template('sales/contacts/email.html.twig')]
    public function email(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $extranetUser)
    {
        $extranetUserEmailForm = $this->container->get('form.factory')->createNamed('xu_email', ExtranetUserEmailType::class, [], ['extranet_user' => $extranetUser['@id']]);

        $client = $this->container->get(Client::class);
        $currentUser = $client->get('/me');
        $extranetUserEmailForm->handleRequest($request);
        if ($extranetUserEmailForm->isSubmitted() && $extranetUserEmailForm->isValid()) {
            try {
                $data = $extranetUserEmailForm->getData();
                $payload = [];

                foreach ($data as $key => $value) {
                    $payload[$key] = $value;
                }

                $client->request(self::RESOURCE_URL, $extranetUser->getIriId(), 'confirmation_mail', 'PUT',
                    [
                        'json' => $payload,
                    ]);
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('contacts.messages.success.email', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('sales_contact_show', ['id' => $extranetUser['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $extranetUserEmailForm);
            }
        }

        return [
            'current_user' => $currentUser,
            'extranetUser' => $extranetUser,
            'form' => $extranetUserEmailForm->createView(),
        ];
    }

    #[Route(path: '/add', name: 'sales_contact_add', methods: ['GET|POST'])]
    #[Template('sales/contacts/add.html.twig')]
    #[IsGranted('FEATURE_EXTRANET_USER_CREATE')]
    public function add(Request $request)
    {
        $form = $this
            ->container
            ->get('form.factory')
            ->createNamed(
                'xu_add_form',
                ExtranetUserType::class,
                [],
                [
                    'add' => true,
                    'addProfile' => true,
                ]
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $xuPayload = $form->getData();

                $xuPayload['username'] = $xuPayload['email'];
                $xuPayload['hidden'] = false;
                $extranetUser = $this->container->get(Client::class)->save(self::RESOURCE_URL, $xuPayload);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('contacts.messages.success.add', ['%id%' => $extranetUser['id']], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('sales_contact_show', ['id' => $extranetUser['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/unused_accounts', name: 'sales_contact_unused_accounts', methods: ['GET'])]
    #[Template('sales/contacts/unused_accounts.html.twig')]
    public function showUnusedAccounts()
    {
        try {
            $extranetUserUnused = $this->container->get(Client::class)->findBy(self::RESOURCE_URL,
                [
                    'lastLogin[before]' => (new \DateTime('90 days ago'))->format('Y-m-d'),
                    'disabled' => false,
                    'normalization_groups' => ['extranet_user_acls'],
                ]);
        } catch (ClientException $e) {
            return 'Could not get Extranet Users Unused. Reason : '.$e->getMessage();
        }

        return [
            'extranetUserUnused' => $extranetUserUnused,
        ];
    }

    #[Route(path: '/roles', name: 'sales_contact_roles', methods: ['GET'])]
    #[Template('sales/contacts/roles.html.twig')]
    public function showXuRoles(#[ApiValueResolverAttribute(parameters: ['resource' => self::GROUP_RESOURCE_URL])] HydraCollection $extranetUserGroups)
    {
        return ['extranetUserGroups' => $extranetUserGroups];
    }

    #[Route(path: '/roles/{id}/members', name: 'sales_contact_roles_members', methods: ['GET'])]
    #[Template('sales/contacts/roles_members.html.twig')]
    public function showXuRolesMembers(#[ApiValueResolverAttribute(parameters: ['resource' => self::GROUP_RESOURCE_URL])] ApiData $extranetUserGroup)
    {
        return [
            'members' => $this->container->get(Client::class)->findBy(self::RESOURCE_URL, ['extranetUserAcls.extranetUserGroup' => $extranetUserGroup['@id']]),
            'group' => $extranetUserGroup['name'],
        ];
    }

    #[Route(path: '/{id}/extranet_request', name: 'sales_contact_extranet_request', methods: ['GET|POST'])]
    public function extranetRequest(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $extranetUser): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->request(self::RESOURCE_URL, $extranetUser->getIriId(), 'extranet_request', 'POST', []);
            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('contacts.messages.success.request', [], self::TRANSLATION_DOMAIN)
            );
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. Reason :%s', $this->container->get(TranslatorInterface::class)->trans('contacts.messages.error.request', [], self::TRANSLATION_DOMAIN), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('sales_contact_show', ['id' => $extranetUser['id']]);
    }

    #[Route(path: '/{id}/delete', name: 'sales_contact_delete', methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_EXTRANET_USER_DELETE')]
    public function delete($id): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $id);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('contacts.messages.success.delete', [], self::TRANSLATION_DOMAIN)
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('contacts.messages.error.delete', [], self::TRANSLATION_DOMAIN)
            );
        }

        return $this->redirectToRoute('sales_contact_home');
    }

    #[Route(path: '/{id}/{action}', name: 'sales_contact_disable_account', methods: ['GET'], requirements: ['action' => 'disable-account|archive-account', 'id' => '\d+'])]
    public function disableAccount(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $extranetUser, string $action): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->request(self::RESOURCE_URL, $extranetUser->getIriId(), str_replace('-', '_', $action), Request::METHOD_PUT);
            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('contacts.messages.success.edit', [], self::TRANSLATION_DOMAIN)
            );
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('contacts.messages.error.edit', [], self::TRANSLATION_DOMAIN), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('sales_contact_show', ['id' => $extranetUser['id']]);
    }

    #[Route(path: '/{id}/vcard', name: 'sales_contact_vcard', methods: 'GET')]
    public function vCard(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $extranetUser)
    {
        $extranetUserVCardNormalizer = $this->container->get(DenormalizerInterface::class);

        /** @var VCard $vCard */
        $vCard = $extranetUserVCardNormalizer->denormalize($extranetUser, VCard::class);
        $filename = filter_var("vCard_{$extranetUser['lastname']}-{$extranetUser['firstname']}.vcf", \FILTER_SANITIZE_ENCODED);

        return new Response(
            $vCard->serialize(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/x-vcard;charset=UTF-8',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
            ]
        );
    }
}
