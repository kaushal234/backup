<?php

declare(strict_types=1);

namespace AppBundle\Controller\Parts\PartNumberTask;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\Controller\Task\TaskController;
use AppBundle\Form\Type\Parts\PartNumberTaskType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/parts/part-number-tasks', defaults: ['alvest_module' => 'PNT', 'moduleDomain' => 'task'])]
class PartNumberTaskEditController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly FormFactoryInterface $formFactory,
        private readonly ViolationMapper $violationMapper,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/{id}/edit', name: 'part_number_task_edit', methods: ['GET', 'POST'])]
    #[Template('parts/part_number_tasks/edit.html.twig')]
    public function edit(Request $request, int $id): array|Response
    {
        $partNumberTask = $this->client->get(\sprintf('%s/%d', TaskController::RESOURCE_URL, $id));

        $form = $this->formFactory->create(PartNumberTaskType::class, $partNumberTask, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $submittedData = $form->getNormData();

                $payload = array_merge(
                    $submittedData,
                    [
                        '@id' => $partNumberTask['@id'],
                        'module' => $partNumberTask['module']['@id'],
                        'referenceId' => (int) basename($submittedData['location']),
                        'partNumber' => $submittedData['partNumber'],
                    ]
                );

                $this->client->save(PartNumberTaskController::RESOURCE_URL, $payload);

                $this->addFlash('success', $this->translator->trans('task.message.edit', [], 'task'));

                return $this->redirectToRoute('part_number_task_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'task' => $partNumberTask,
        ];
    }
}
