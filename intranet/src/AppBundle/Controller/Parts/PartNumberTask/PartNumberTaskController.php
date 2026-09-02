<?php

declare(strict_types=1);

namespace AppBundle\Controller\Parts\PartNumberTask;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Parts\PartNumberTaskDatatableType;
use AppBundle\Form\Type\Parts\PartNumberTaskType;
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

#[Route(path: '/parts/part-number-tasks', defaults: ['alvest_module' => 'PNT', 'moduleDomain' => 'task'])]
class PartNumberTaskController extends AbstractController
{
    use DataTableFactoryAwareTrait;

    public const string MODULE_PNT = 'PNT';
    public const string RESOURCE_URL = 'part_number_tasks';

    public function __construct(
        private readonly Client $client,
        private readonly FormFactoryInterface $formFactory,
        private readonly TranslatorInterface $translator,
        private readonly ViolationMapper $violationMapper,
    ) {
    }

    #[Route('', name: 'part_number_task_home', methods: ['GET', 'POST'])]
    #[Template('parts/part_number_tasks/index.html.twig')]
    public function index(Request $request): array|Response
    {
        $form = $this->formFactory->create(PartNumberTaskType::class, [
            'shortDescription' => \sprintf('SMW Meeting %s', (new \DateTime())->format('Y-m-d')),
        ]);

        $form->handleRequest($request);
        $module = $this->client->findBy('modules', ['name' => self::MODULE_PNT])[0] ?? null;

        if (null === $module) {
            $this->addFlash('error', $this->translator->trans('part_number_task.error.module_code_not_found', [], 'task'));

            return $this->redirectToRoute('part_number_task_home');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            try {
                $payload = [
                    ...$data,
                    'module' => $module->getIri(),
                    'referenceId' => (int) basename($data['location']),
                ];

                $response = $this->client->post(self::RESOURCE_URL, [
                    'json' => $payload,
                ]);

                $partNumberTaskId = $response['id'] ?? null;
                $file = $form->get('file')->getData();

                if ($partNumberTaskId && $file instanceof UploadedFile) {
                    $formData = new FormDataPart([
                        'file' => DataPart::fromPath($file->getPathname()),
                    ]);

                    $this->client->post(\sprintf('tasks/%d/files', $partNumberTaskId), [
                        'headers' => $formData->getPreparedHeaders()->toArray(),
                        'body' => $formData->bodyToIterable(),
                    ]);
                }

                $this->addFlash('success', $this->translator->trans('task.message.add', [], 'task'));

                return $this->redirectToRoute('part_number_task_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        $datatable = $this->createDataTable(PartNumberTaskDatatableType::class, self::RESOURCE_URL.'?module='.$module->getIri());
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
