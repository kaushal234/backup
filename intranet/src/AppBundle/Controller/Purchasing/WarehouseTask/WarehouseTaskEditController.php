<?php

declare(strict_types=1);

namespace AppBundle\Controller\Purchasing\WarehouseTask;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Controller\Task\TaskController;
use AppBundle\Form\Type\Purchasing\WarehouseTask\WarehouseTaskType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/purchasing/warehouse-tasks', defaults: ['alvest_module' => 'WHT', 'moduleDomain' => 'warehouse_task'])]
class WarehouseTaskEditController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly FormFactoryInterface $formFactory,
        private readonly ViolationMapper $violationMapper,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/{id}/edit', name: 'warehouse_task_edit', methods: ['GET', 'POST'])]
    #[Template('purchasing/warehouse_task/edit.html.twig')]
    public function edit(Request $request, int $id): array|Response
    {
        $warehouseTask = $this->client->get(\sprintf('%s/%d', TaskController::RESOURCE_URL, $id));
        $form = $this->formFactory->create(WarehouseTaskType::class, $warehouseTask, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $payload = [
                    '@id' => $warehouseTask['@id'],
                    'location' => $data['location'],
                    'assignee' => $data['assignee'],
                    'shortDescription' => $data['shortDescription'],
                    'description' => $data['description'],
                    'dueDate' => $data['dueDate'],
                    'startedAt' => $data['startedAt'],
                    'indiceFactor' => $data['indiceFactor'],
                    'recipients' => $data['recipients'],
                    'referenceId' => (int) basename($data['location']),
                ];
                $this->client->save(TaskController::RESOURCE_URL, $payload);

                $this->addFlash('success', $this->translator->trans('task.message.edit', [], 'task'));

                return $this->redirectToRoute('warehouse_task_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'warehouseTask' => $warehouseTask,
        ];
    }
}
