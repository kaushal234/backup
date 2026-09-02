<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Directory\ProfileUpdateTaskDataTableType;
use AppBundle\DataTable\Type\Mis\GuestUser\GuestUserDataTableType;
use AppBundle\Form\Type\Mis\GuestUser\GuestUserType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/guest-users', defaults: ['alvest_module' => 'Guest User', 'moduleDomain' => 'mis_guest_user'])]
class GuestUserController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    public const string RESOURCE_URL = 'guest_users';

    public function __construct(
        private readonly Client $client,
        private readonly ViolationMapper $violationMapper,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '', name: 'mis_guest_user_home', methods: ['GET', 'POST'])]
    #[Template('mis/guest_user/home.html.twig')]
    public function home(Request $request)
    {
        $datatable = $this->createDataTable(GuestUserDataTableType::class, GuestUserDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        return [
            'datatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'mis_guest_user_show', methods: ['GET', 'POST'])]
    #[Template('mis/guest_user/show.html.twig')]
    public function show(
        #[ApiValueResolverAttribute] ApiData $guestUser,
        Request $request,
    ) {
        $updateTasksDataTable = $this->createDataTable(
            ProfileUpdateTaskDataTableType::class,
            ProfileUpdateTaskDataTableType::RESOURCE,
            ['user' => $guestUser->getIri()],
        );

        if ($updateTasksDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($updateTasksDataTable);
        }

        $numberOfTasksInProgress = 0;
        if ($this->isGranted('FEATURE_MODULE_WRITE')) {
            $updateTasksInProgress = $this->client->findBy('modules/third_party_app/update_tasks', [
                'user' => $guestUser->getIriId(),
                'done' => false,
            ]);

            $numberOfTasksInProgress = \count($updateTasksInProgress);
        }

        $updateTasksDataTable->handleRequest($request);

        return [
            'guestUser' => $guestUser,
            'updateTasksDataTable' => $updateTasksDataTable->createView(),
            'numberOfTasksInProgress' => $numberOfTasksInProgress,
        ];
    }

    #[Route(path: '/add', name: 'mis_guest_user_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'mis_guest_user_edit', methods: ['GET', 'POST'])]
    #[Template('mis/guest_user/write.html.twig')]
    public function write(Request $request, #[ApiValueResolverAttribute] ?ApiData $guestUser = null)
    {
        $user = $this->client->get('/me');
        if (null !== $guestUser) {
            $this->denyAccessUnlessGranted('GUEST_USER_UPDATE_VOTER', $guestUser['@id']);
        }
        $form = $this->createForm(GuestUserType::class, $guestUser, [
            'add' => null === $guestUser,
            'default_business_unit' => null === $guestUser ? $user['businessUnit']['@id'] : null,
            'default_premise' => null === $guestUser ? $user['premise'] : null,
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                if (null !== $guestUser) {
                    unset($data['position']);
                }

                $user = $this->client->save(self::RESOURCE_URL, $data);

                $this->addFlash('success', $this->translator->trans(null === $guestUser ? 'mis.guest_user.add.success' : 'mis.guest_user.edit.success', [], 'mis'));

                return $this->redirectToRoute('mis_guest_user_show', ['id' => Iri::id($user)]);
            } catch (ClientException $exception) {
                $this->violationMapper->mapToForm($exception, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'title' => null === $guestUser ? 'mis.guest_user.add.title' : 'mis.guest_user.edit.title',
        ];
    }
}
