<?php

declare(strict_types=1);

namespace AppBundle\Controller\Account;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Persistence\DataTablePersistenceClearerInterface;
use AppBundle\DataTable\Persistence\UserModulePersistenceSubjectAggregate;
use AppBundle\Form\Type\Account\AccountPasswordType;
use AppBundle\Form\Type\Account\AccountType;
use AppBundle\Manager\FileManager;
use AppBundle\Manager\UserManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

#[Route(path: '/account', defaults: ['alvest_module' => 'USER', 'breadcrumb_label' => 'menu.my_account', 'moduleDomain' => 'account'])]
class AccountController extends AbstractController
{
    use TargetPathTrait;

    private readonly UserManager $userManager;

    private readonly FileManager $fileManager;

    private readonly ViolationMapper $violationMapper;

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    private readonly Client $client;

    public function __construct(UserManager $userManager, ViolationMapper $violationMapper, AuthorizationCheckerInterface $authorizationChecker, Client $client, FileManager $fileManager)
    {
        $this->userManager = $userManager;
        $this->fileManager = $fileManager;
        $this->violationMapper = $violationMapper;
        $this->authorizationChecker = $authorizationChecker;
        $this->client = $client;
    }

    #[Route(path: '', name: 'account_home', methods: 'GET')]
    #[Template('account/index.html.twig')]
    public function index()
    {
        $account = $this->getConnectedPeople();

        return [
            'account' => $account,
            'people' => $account->getIriId(),
        ];
    }

    #[Route(path: '/{id}/acls', name: 'account_show', methods: ['GET', 'DELETE'], requirements: ['id' => '\d+'], defaults: ['label' => 'account.menu.acls', 'domain' => 'account'])]
    #[Template('account/acls.html.twig')]
    #[IsGranted(attribute: 'FEATURE_PEOPLE_ACL_VOTER', subject: new Expression('args["people"].getIri()'))]
    public function acls(#[ApiValueResolverAttribute(parameters: ['resource' => 'people', 'filters' => ['normalization_groups' => ['group_member']]])] ApiData $people)
    {
        $account = $this->getConnectedPeople();

        return [
            'acls' => $people['acls'],
            'userId' => $people->getIriId(),
            'people' => $people,
            'account' => $account,
        ];
    }

    #[Route(path: '/edit', name: 'account_edit', methods: 'GET|POST', defaults: ['label' => 'account.menu.edit_my_account', 'domain' => 'account'])]
    #[Template('account/edit.html.twig')]
    public function edit(Request $request)
    {
        $account = $this->getConnectedPeople();
        $authorizedFields = $this->client->get('/fields', ['query' => ['iri' => $account['@id'], 'method' => 'PUT']]);

        $authorizedFields[] = 'image';
        $form = $this->createForm(
            AccountType::class,
            $account,
            [
                'authorizedFields' => $authorizedFields,
            ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var ApiData $peopleData */
                $peopleData = $form->getData();
                $peopleData = array_intersect_key($peopleData->toArray(), array_flip(AccountType::$fieldsToKeep) + ['@id' => true, '@type' => true]);
                unset($peopleData['businessUnit'], $peopleData['position'], $peopleData['department'], $peopleData['supervisor'], $peopleData['mentor']);
                $imageData = $form->get('image')->getData();

                $people = $this->userManager->updatePeople($peopleData);

                $fileId = $people['photo']['id'] ?? null;
                $this->fileManager->updateImage($people, 'people', $imageData, 'photo', $fileId, true);

                $this->addFlash('success', 'Account updated successfully');

                return $this->redirectToRoute('account_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'people' => $account->getIriId(),
            'account' => $account,
        ];
    }

    #[Route(path: '/change-password', name: 'account_change_password', methods: 'GET|POST', defaults: ['label' => 'account.menu.change_my_password', 'domain' => 'account'])]
    #[Template('account/change_password.html.twig')]
    public function changePassword(Request $request, KernelInterface $kernel): array|RedirectResponse
    {
        if (!$this->authorizationChecker->isGranted('ROLE_AUTHENTICATED_FRESH') && 'test' !== $kernel->getEnvironment()) {
            $this->addFlash('warning', 'Please re authenticate to confirm your identity');

            $this->saveTargetPath($request->getSession(), 'main', $request->getUri());

            return $this->redirectToRoute('login');
        }

        $account = $this->getConnectedPeople();
        $form = $this->createForm(AccountPasswordType::class, [
            '@id' => $account['@id'],
            '@type' => $account['@type'],
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userManager->updatePeople($form->getData());

                $username = $this->getUser()->getUserIdentifier();
                $plainPassword = $form->getData()['password'] ?? null;

                $this->userManager->reloadUser($username, $plainPassword);

                $this->addFlash('success', 'Password changed successfully');

                return $this->redirectToRoute('account_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form, ['clearPassword' => 'password.first']);
            }
        }

        return [
            'form' => $form->createView(),
            'people' => $account->getIriId(),
            'account' => $account,
        ];
    }

    /**
     * Clear users personalization and cache on a given datatable.
     * This route is called on the reset datatable button.
     */
    #[Route('/clear-datatable-persistence', name: 'account_clear_datatable_persistence', methods: 'POST')]
    public function clearDataTablePersistence(DataTablePersistenceClearerInterface $persistenceClearer, Request $request): RedirectResponse
    {
        $moduleName = $request->query->get('module_name');
        $dataTableName = $request->query->get('datatable_name');

        /** @var User $user */
        $user = $this->getUser();

        if (null === $moduleName || null === $dataTableName) {
            throw new \InvalidArgumentException('Missing module_name or datatable_name parameters.');
        }

        $persistenceClearer->clear(new UserModulePersistenceSubjectAggregate(
            $user->getId(),
            $moduleName,
        ), $dataTableName);

        // Redirect without params (filters, pagination etc.) to reset datatable.
        $refererWithoutParams = parse_url($request->headers->get('referer'), \PHP_URL_PATH);

        return new RedirectResponse($refererWithoutParams);
    }

    private function getConnectedPeople()
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->client->find('people', $user->getId());
    }
}
