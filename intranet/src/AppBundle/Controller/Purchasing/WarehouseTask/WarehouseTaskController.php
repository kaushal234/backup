<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\WarehouseTask;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Controller\Task\TaskController;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Purchasing\WarehouseTaskDataTableType;
use AppBundle\Form\Type\Purchasing\WarehouseTask\WarehouseTaskType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/warehouse-tasks', defaults: ['alvest_module' => 'WHT', 'moduleDomain' => 'warehouse_task'])]
class WarehouseTaskController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    public const string MODULE_WHT = 'WHT';

    public function __construct(
        private readonly Client $client,
        private readonly FormFactoryInterface $formFactory,
        private readonly TranslatorInterface $translator,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route('', name: 'warehouse_task_home', methods: ['GET', 'POST'])]
    #[Template('purchasing/warehouse_task/index.html.twig')]
    public function index(Request $request): array|Response
    {
        $form = $this->formFactory->create(WarehouseTaskType::class, [
            'shortDescription' => \sprintf('SMW Meeting %s', (new \DateTime())->format('Y-m-d')),
        ]);

        $form->handleRequest($request);
        $module = $this->client->findBy('modules', ['name' => self::MODULE_WHT])[0] ?? null;

        if (null === $module) {
            $this->addFlash('error', $this->translator->trans('warehouse_task.error.module_code_not_found', [], 'task'));

            return $this->redirectToRoute('warehouse_task_home');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                $payload = [
                    ...$data,
                    'module' => $module->getIri(),
                    'referenceId' => (int) basename($data['location']),
                ];

                $response = $this->client->post(TaskController::RESOURCE_URL, [
                    'json' => $payload,
                ]);

                $warehouseTaskId = $response['id'] ?? null;
                $file = $form->get('file')->getData();

                if ($warehouseTaskId && $file instanceof UploadedFile) {
                    $formData = new FormDataPart([
                        'file' => DataPart::fromPath($file->getPathname()),
                    ]);

                    $this->client->post(\sprintf('tasks/%d/files', $warehouseTaskId), [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);
                }

                $this->addFlash('success', $this->translator->trans('task.message.add', [], 'task'));

                return $this->redirectToRoute('warehouse_task_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        $datatable = $this->createDataTable(WarehouseTaskDataTableType::class, TaskController::RESOURCE_URL.'?module='.$module->getIri());
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }

        return [
            'form' => $form->createView(),
            'datatable' => $datatable->createView(),
        ];
    }
}
