<?php

declare(strict_types=1);

namespace AppBundle\Controller\Materials\Warehouse;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Materials\Warehouse\TasksMappingType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/materials/warehouse/tasks-mappings', defaults: ['alvest_module' => 'WHSE', 'moduleDomain' => 'warehouse_tasks_mapping'])]
class TasksMappingController extends AbstractController
{
    #[Route(path: '', name: 'warehouse_tasks_mapping_home', methods: ['GET'])]
    public function home(#[ApiValueResolverAttribute(parameters: ['resource' => 'materials/warehouse/tasks_mappings'])] HydraCollection $tasksMappings): Response
    {
        return $this->render('materials/warehouse/tasks_mappings/list.html.twig', [
            'mappings' => $tasksMappings,
        ]);
    }

    #[Route(path: '/add', name: 'warehouse_tasks_mapping_add', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/edit', name: 'warehouse_tasks_mapping_edit', methods: ['GET', 'POST'])]
    #[Route(path: '/{id}/show', name: 'warehouse_tasks_mapping_show', methods: ['GET', 'POST'])]
    public function addEdit(
        Request $request,
        ViolationMapper $violationMapper,
        Client $client,
        TranslatorInterface $translator,
        #[ApiValueResolverAttribute(parameters: ['resource' => 'materials/warehouse/tasks_mappings'])] ?ApiData $tasksMapping = null): Response
    {
        if (!$this->isGranted('TASKS_MAPPING_WRITE_VOTER', $tasksMapping['@id'] ?? null)) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(TasksMappingType::class, $tasksMapping);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client->save('materials/warehouse/tasks_mappings', $form->getData());

                $this->addFlash('success', ucfirst($translator->trans(null !== $tasksMapping ? 'materials.tasks_mappings.messages.success.edit' : 'materials.tasks_mappings.messages.success.add', [], 'materials')));

                return $this->redirectToRoute('warehouse_tasks_mapping_home');
            } catch (ClientException $e) {
                $violationMapper->mapToForm($e, $form);
            }
        }

        return $this->render('materials/warehouse/tasks_mappings/add_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
