<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis;

use ApiBundle\Client;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Jira\IssueType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: 'mis/modules/{moduleId}/specifications', defaults: ['alvest_module' => 'MOD', 'breadcrumb_label' => 'menu.modules.title', 'moduleDomain' => 'mis_modules'])]
class SpecificationController extends AbstractController
{
    public const RESOURCE_URL_MODULE = 'modules';
    public const RESOURCE_URL_SPECIFICATION = 'mis/specifications';
    public const RESOURCE_URL_USER_STORY = 'mis/user_stories';
    public const JIRA_ISSUE_URL = 'jira/user_story_issues';

    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), Client::class, TranslatorInterface::class, FileStreamedResponseFactory::class];
    }

    #[Route(path: '/add', name: 'specification_add', methods: 'GET')]
    public function addSpecification(
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_MODULE, 'id' => 'moduleId'])] ApiData $module,
    ): RedirectResponse {
        try {
            $client = $this->container->get(Client::class);
            $specification = $client->save(self::RESOURCE_URL_SPECIFICATION, ['module' => $module['@id']]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('mis.specification.add_success', [], 'mis'));

            return $this->redirectToRoute('specification_show', [
                'moduleId' => $module['id'],
                'id' => $specification['id'],
            ]);
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getContent(false), true);
            foreach ($errors['violations'] as $error) {
                $this->addFlash('warning', $error['message']);
            }
        }

        return $this->redirectToRoute('mis_modules_show', [
            'id' => $module['id'],
        ]);
    }

    #[Route(path: '/{id}/show', name: 'specification_show', defaults: ['label' => 'mis.specification.specification', 'domain' => 'mis'], methods: 'GET')]
    #[Template('mis/specification/user_stories.html.twig')]
    public function showSpecification(
        #[CurrentUser] User $user,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_MODULE, 'id' => 'moduleId'])] ApiData $module,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_SPECIFICATION, 'filters' => ['normalizationGroups' => ['workflow']]])] ApiData $specification,
    ): array {
        $lku = null;

        if (isset($module['localKeyUsers'])) {
            foreach ($module['localKeyUsers'] as $localKeyUsers) {
                $lku = $user->getIriId() === $localKeyUsers['@id'];
            }
        }
        $props = [
            'moduleName' => $module['name'],
            'moduleId' => $module['id'],
            'specificationId' => $specification['id'],
            'mis' => $this->isGranted('ACL_GG_MIS'),
            'moo' => isset($module['operationalOwner']['@id']) && $user->getIriId() === $module['operationalOwner']['@id'],
            'mku' => isset($module['keyUser']['@id']) && $user->getIriId() === $module['keyUser']['@id'],
            'lku' => $lku,
        ];

        return [
            'moduleName' => $module['name'],
            'props' => $props,
        ];
    }

    #[Route(path: '/{id}/files', name: 'user_story_files', defaults: ['label' => 'mis.specification.files', 'domain' => 'mis', 'parent_route' => 'specification_show', 'parent_label' => 'Specification', 'parent_id' => 'moduleId'])]
    #[Template('mis/specification/files.html.twig')]
    public function files(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_USER_STORY])] ApiData $userStory, int $moduleId): array
    {
        return [
            'moduleId' => $moduleId,
            'userStory' => $userStory,
        ];
    }

    #[Route(path: '/{id}/files_ajax', name: 'files_ajax_show', methods: 'GET')]
    #[Template('mis/specification/files_ajax.html.twig')]
    public function userStoryFilesAjax(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_USER_STORY])] ApiData $userStory, int $moduleId): array
    {
        return [
            'moduleId' => $moduleId,
            'userStory' => $userStory,
        ];
    }

    #[Route(path: '/{userStoryId}/files/{id}', name: 'user_story_files_show', methods: 'GET')]
    public function showFile($userStoryId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('mis/user_stories/%s/files/%s', $userStoryId, $id));
    }

    #[Route(path: '/{userStoryId}/files/{id}/delete', name: 'delete_user_story_file', methods: ['GET'])]
    public function deleteFile(Request $request, $userStoryId, $id, int $moduleId): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_user_story_file', $request->query->get('_token'))) {
            $this->addFlash('error', 'Cannot delete file: please refresh your form.');

            return $this->redirectToRoute('user_story_files', ['id' => $userStoryId]);
        }

        $operation = \sprintf('files/%s', $id);

        try {
            $this->container->get(Client::class)->request(self::RESOURCE_URL_USER_STORY, $userStoryId, $operation, Request::METHOD_DELETE);
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('first_article_qualification.messages.error.delete_file', [], 'first_article_qualification')
            );
        }

        return $this->redirectToRoute('user_story_files', [
            'moduleId' => $moduleId,
            'id' => $userStoryId,
        ]);
    }

    #[Route(path: '/{id}/{userStoryId}/transfer-to-jira', name: 'user_story_transfer_jira', defaults: ['label' => 'mis.specification.jira', 'domain' => 'mis'], methods: ['GET|POST'])]
    #[Template('mis/specification/jira_transfer.html.twig')]
    #[IsGranted('ACL_GG_MIS')]
    public function transferToJira(
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_SPECIFICATION])] ApiData $specification,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL_USER_STORY, 'id' => 'userStoryId'])] ApiData $userStory,
        Request $request): RedirectResponse|array
    {
        $form = $this->createForm(IssueType::class, [], [
            'description' => $userStory['description'],
            'projectId' => $userStory['projectNumber'],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payload = array_merge($form->getData(), ['userStoryId' => $userStory->getIriId()]);
            try {
                $this->container->get(Client::class)->save(self::JIRA_ISSUE_URL, $payload);

                return $this->redirectToRoute('specification_show', ['moduleId' => $specification['module']['id'], 'id' => $specification['id']]);
            } catch (ClientException $e) {
            }
        }

        return [
            'specification' => $specification,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/downloadXLSX', name: 'specification_downloadXLSX')]
    #[IsGranted('ACL_GG_MIS')]
    public function downloadXLSX(int $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(
            \sprintf('mis/user_stories?specification=%d', $id),
            [
                'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ],
            'specification_'.$id.'.xlsx'
        );
    }
}
