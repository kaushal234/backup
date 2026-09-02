<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\Project;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Http\ZipStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Mis\Project\ProjectDataTableType;
use AppBundle\Form\Type\Mis\Project\ProjectType;
use AppBundle\Form\Type\Mis\Project\ProjectUpdateStatusType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\Filter\FiltrationData;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/mis/projects', defaults: ['alvest_module' => 'MIS', 'breadcrumb_label' => 'menu.mis_project.title', 'moduleDomain' => 'mis_project'])]
class ProjectController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public const RESOURCE_URL = 'mis/projects';
    final public const MIS_PROJECT_FILE_URL = 'mis/projects';

    public const TRANSLATION_DOMAIN = 'mis_project';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            ViolationMapper::class,
            FileManager::class,
            ZipStreamedResponseFactory::class,
            FileStreamedResponseFactory::class,
        ]);
    }

    #[Route(path: '', name: 'mis_project_home', methods: ['GET', 'POST'])]
    #[Template('mis/project/home.html.twig')]
    public function home(Request $request)
    {
        $projectDatatable = $this->createDataTable(ProjectDataTableType::class, self::RESOURCE_URL);
        $projectDatatable->setFiltrationData(FiltrationData::fromArray([
            'status' => ['PENDING', 'CANCELLED', 'PHASE 0', 'PHASE 1', 'PHASE 2', 'PHASE 3', 'PHASE 4'],
        ]));
        $projectDatatable->handleRequest($request);
        if ($projectDatatable->isExporting() && $projectDatatable->getQuery() instanceof ApiProxyQuery) {
            return $projectDatatable->getQuery()->export();
        }

        return [
            'projectDatatable' => $projectDatatable->createView(),
        ];
    }

    #[Route(path: '/add', name: 'mis_project_add', methods: ['GET', 'POST'])]
    #[IsGranted('FEATURE_MIS_PROJECT_WRITE')]
    public function newProject(Request $request): Response
    {
        $initialData = [
            'phases' => [
                ['estimatedClosureAt' => null, 'estimatedHours' => null, 'number' => 0],
                ['estimatedClosureAt' => null, 'estimatedHours' => null, 'number' => 1],
                ['estimatedClosureAt' => null, 'estimatedHours' => null, 'number' => 2],
                ['estimatedClosureAt' => null, 'estimatedHours' => null, 'number' => 3],
                ['estimatedClosureAt' => null, 'estimatedHours' => null, 'number' => 4],
            ],
        ];
        $form = $this->createForm(ProjectType::class, $initialData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $payload = $form->getData();
                $response = $this->container->get(Client::class)->save(self::RESOURCE_URL, $payload);
                $this->addFlash('success', $this->translator->trans(
                    'mis_project.message.success.add',
                    [],
                    self::TRANSLATION_DOMAIN
                ));

                return $this->redirectToRoute('mis_project_show', ['id' => $response['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return $this->render('mis/project/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/show', name: 'mis_project_show', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/update-status', name: 'mis_project_update_status', defaults: ['label' => 'mis_project.fields.update_status'], methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/close', name: 'mis_project_close', defaults: ['label' => 'mis_project.fields.close'], methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/comment', name: 'mis_project_comment', defaults: ['label' => 'mis_project.fields.comment'], methods: ['GET', 'POST'])]
    #[Template('mis/project/show.html.twig')]
    public function show(
        Request $request,
        #[ApiValueResolverAttribute(parameters: [
            'resource' => self::RESOURCE_URL,
            'filters' => ['normalizationGroups' => ['workflow']],
        ])] ApiData $project)
    {
        $client = $this->container->get(Client::class);

        $close = false;
        $status = false;
        $comment = false;
        $availableStatuses = [];
        $action = null;
        $title = '';
        switch ($route = $request->attributes->get('_route')) {
            case 'mis_project_update_status':
                if (!$this->isGranted('PROJECT_EDIT_VOTER', $project->getIri())) {
                    throw new AccessDeniedHttpException();
                }
                $status = true;
                $availableStatuses = $project['availableStatus'];
                $action = 'status';
                $title = 'update_status';
                break;
            case 'mis_project_close':
                if (!$this->isGranted('PROJECT_CLOSE_VOTER', $project->getIri())) {
                    throw new AccessDeniedHttpException();
                }
                $close = true;
                $action = $title = 'close';
                break;
            case 'mis_project_comment':
                $comment = true;
                $action = $title = 'comment';
                break;
            default:
                // route "show", do nothing
        }

        $commentForm = null;
        if ('mis_project_show' !== $request->attributes->get('_route')) {
            $commentForm = $this->createForm(ProjectUpdateStatusType::class, [], ['availableStatus' => $availableStatuses, 'close' => $close, 'status' => $status, 'comment' => $comment]);

            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $data = $commentForm->getData();

                switch ($route) {
                    case 'mis_project_update_status':
                        $payload = [
                            'comment' => $data['comment'],
                            'status' => $data['status'],
                        ];
                        break;
                    case 'mis_project_comment':
                        $payload = [
                            'comment' => $data['comment'],
                        ];
                        break;
                    default:
                        // default to close route
                        $payload = [
                            'conclusion' => $data['conclusion'],
                            'status' => 'CLOSED',
                        ];
                }

                if ($data['file'] instanceof UploadedFile) {
                    $payload['file'] = DataPart::fromPath($data['file']->getPathname());
                }

                $formData = new FormDataPart($payload);
                try {
                    $client->post(\sprintf('mis/projects/%d/%s', $project->getIriId(), $action), [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);

                    return $this->redirectToRoute('mis_project_show', ['id' => $project->getIriId()]);
                } catch (ClientException $exception) {
                    $this->container->get(ViolationMapper::class)->mapToForm($exception, $commentForm);
                }
            }
        }

        return [
            'commentForm' => $commentForm,
            'filesBadgeCount' => \count($project['files']),
            'project' => $project,
            'tasks' => $client->findBy('tasks', ['module.name' => 'MIS', 'referenceId' => $project['id']]),
            'title' => $title,
            'comments' => $client->findBy('/comments', ['resource' => $project->getIri(), 'normalization_groups' => ['people_photo']], ['createdAt' => 'DESC']),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'mis_project_edit', methods: ['GET', 'POST'])]
    #[IsGranted(
        attribute: new Expression("is_granted('PROJECT_EDIT_VOTER', subject['project'].getIri()) or is_granted('FEATURE_MIS_PROJECT_CIO_EDIT')"),
        subject: ['project' => new Expression('args["project"]')]
    )]
    public function edit(
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])]
        ApiData $project,
        Request $request,
    ): Response {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $payload = $form->getData();
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $payload);
                $this->addFlash('success', $this->translator->trans(
                    'mis_project.message.success.edit',
                    [],
                    self::TRANSLATION_DOMAIN
                ));

                return $this->redirectToRoute('mis_project_show', ['id' => $project['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return $this->render('mis/project/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{misProjectId}/files/{id}', name: 'mis_project_files_show', requirements: ['id' => '\d+'], methods: 'GET')]
    public function showFile($misProjectId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::MIS_PROJECT_FILE_URL, $misProjectId, $id));
    }

    #[Route(path: '/{misProjectId}/files/download-all', name: 'mis_project_zip_files', requirements: ['misProjectId' => '\d+'], methods: 'GET')]
    public function downloadAllFile(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'misProjectId'])] ApiData $project)
    {
        return $this->container->get(ZipStreamedResponseFactory::class)->create(\sprintf('%s/%d/files',
            self::MIS_PROJECT_FILE_URL, $project['id']), \sprintf('Project%s', $project['id']));
    }

    #[Route(path: '/{misProjectId}/files/{fileId}/change_visibility', name: 'mis_project_file_change_visibility', methods: 'GET')]
    public function changeFileVisibility(
        #[ApiValueResolverAttribute(
            parameters: ['resource' => 'project_files', 'id' => 'fileId'])] ApiData $projectFile,
        int $fileId, int $misProjectId): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->put(
                \sprintf('files/%d', $fileId),
                ['json' => ['fileId' => $fileId, 'public' => true !== $projectFile['public']]]
            );
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('visibility.success', [], 'file_type'));
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash('error', \sprintf('%s %s', $this->container->get(TranslatorInterface::class)->trans('visibility.errors', [], 'file_type'), $errorDescription['hydra:description']));
        }

        return $this->redirectToRoute('mis_project_show', ['id' => $misProjectId]);
    }

    #[Route(path: '/{misProjectId}/files/{id}/delete', name: 'mis_project_file_delete', methods: ['GET'])]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'misProjectId'])] ApiData $project, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_mis_project_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('mis_project_show', ['id' => $project->getIriId()]);
        }
        $this->container->get(FileManager::class)->deleteFile($project, self::RESOURCE_URL, \sprintf('files/%s',
            $id));
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('delete.success', [], 'file_type'));

        return $this->redirectToRoute('mis_project_show', ['id' => $project->getIriId()]);
    }

    #[Route(path: '/{id}/show_ajax', name: 'mis_project_file_ajax', methods: 'GET')]
    #[Template('mis/project/partial/files_ajax.html.twig')]
    public function projectFilesAjax(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $project)
    {
        return compact('project');
    }
}
