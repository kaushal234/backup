<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Type\PowerBI\ReportDataTableType;
use AppBundle\Form\Type\PowerBI\ReportType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/powerbi-reports', defaults: ['alvest_module' => 'Power BI'])]
class PowerBIReportController extends AbstractController
{
    public const string RESOURCE_URL = 'power_bi/reports';
    public const string DELETE_TOKEN = 'delete_power_bi_report_token_id';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            TranslatorInterface::class,
            ViolationMapper::class,
            FileManager::class,
            FileStreamedResponseFactory::class,
        ]);
    }

    #[Route(path: '/', name: 'power_bi_report_home')]
    public function index(
        Request $request,
        DataTableFactoryInterface $dataTableFactory,
    ): Response {
        $datatable = $dataTableFactory->create(ReportDataTableType::class, self::RESOURCE_URL);
        $datatable->handleRequest($request);

        return $this->render('power_bi/index.html.twig', [
            'datatable' => $datatable->createView(),
        ]);
    }

    #[Route(path: '/{id}/show', name: 'power_bi_report_show', methods: ['GET'])]
    public function show(
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])]
        ApiData $report,
    ): Response {
        return $this->render('power_bi/show.html.twig', [
            'report' => $report,
        ]);
    }

    #[Route(path: '/add', name: 'power_bi_report_add', methods: ['GET|POST'])]
    #[IsGranted('FEATURE_POWER_BI_REPORT_CREATE')]
    public function create(Request $request): Response
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $form = $this->createForm(ReportType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client->save(self::RESOURCE_URL, $form->getData());

                $this->addFlash('success', $translator->trans('power_bi_report.create.success'));

                return $this->redirectToRoute('power_bi_report_home');
            } catch (ClientException $e) {
                $this->addFlash('error', $translator->trans('power_bi_report.create.error'));
                $violationMapper->mapToForm($e, $form);
            }
        }

        return $this->render('power_bi/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'power_bi_report_edit', methods: ['GET|POST'])]
    #[IsGranted('FEATURE_POWER_BI_REPORT_UPDATE')]
    public function edit(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])]
        ApiData $report,
    ): Response {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $violationMapper = $this->container->get(ViolationMapper::class);

        $form = $this->createForm(ReportType::class, $report);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            unset($data['files']);
            try {
                $client->save(self::RESOURCE_URL, $data);
                $this->addFlash('success', $translator->trans('power_bi_report.update.success'));

                return $this->redirectToRoute('power_bi_report_show', ['id' => $report->getIriId()]);
            } catch (ClientException $e) {
                $this->addFlash('error', $translator->trans('power_bi_report.update.error'));
                $violationMapper->mapToForm($e, $form);
            }
        }

        return $this->render('power_bi/edit.html.twig', [
            'report' => $report,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'power_bi_report_delete', methods: ['GET'])]
    #[IsGranted('FEATURE_POWER_BI_REPORT_DELETE')]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $report): RedirectResponse
    {
        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);

        try {
            $client->remove(self::RESOURCE_URL, $report->getIriId());

            $this->addFlash('success', $translator->trans('power_bi_report.delete.success'));
        } catch (ClientException $e) {
            $this->addFlash('error', $translator->trans('power_bi_report.delete.error'));
        }

        return $this->redirectToRoute('power_bi_report_home');
    }

    #[Route(path: '/aes/{name}', name: 'powerbi_aes_report_show', methods: ['GET'])]
    #[Template('powerbi/aes.html.twig')]
    public function showAesReport(string $name): array
    {
        return ['id' => 'parts' === $name ? '5f75678a-f654-4d5b-ac21-90293ae2f081' : '9e7036b0-162a-42a0-b224-f86b460e7ff7'];
    }

    #[Route(path: '/aes/smw/{name}', name: 'powerbi_aes_smw_report_show', methods: ['GET'])]
    #[Template('powerbi/aes_smw.html.twig')]
    public function showAesSmwReport(string $name): array
    {
        $reports = [
            'sales' => ['title' => 'Sales Dashboard', 'reportId' => '9f1bc71c-3332-49d9-a196-6709caf17cf7'],
            'echotech' => ['title' => 'EchoTech Dashboard', 'reportId' => '1c80eecf-66d0-468d-a707-038ced8434b6'],
            'maintenance' => ['title' => 'Maintenance Dashboard', 'reportId' => '4c3629b9-c840-4486-a995-baddf710f967'],
            'finance' => ['title' => 'Finance Dashboard', 'reportId' => 'c87086d3-eb64-4794-8957-86708a8b2ded'],
            'hr' => ['title' => 'HR Dashboard', 'reportId' => '9f708268-a67b-4582-ac02-b2886368118d'],
            'productivity' => ['title' => 'Productivity Dashboard', 'reportId' => '1117f642-685b-4ba6-971b-3f2434f39af8'],
            'inventory' => ['title' => 'Physical Inventory', 'reportId' => '407da370-0b90-4d43-a5ef-17c54c81f733'],
            'qehs' => ['title' => 'QEHS Observation Dashboard', 'reportId' => '972f991c-dac1-4639-98f7-bc4776554f2b'],
            'purchasing' => ['title' => 'Purchasing Dashboard', 'reportId' => '78012178-3086-4433-9516-99cad16ca206'],
        ];

        $report = $reports[$name] ?? throw new NotFoundHttpException(\sprintf('Unknown AES SMW report "%s".', $name));

        if ('productivity' === $name && !$this->isGranted('FEATURE_POWERBI_AES_READ')) {
            throw $this->createAccessDeniedException();
        }

        return [
            'title' => $report['title'],
            'reportId' => $report['reportId'],
            'workspaceId' => '89e02cc1-2a00-4e3d-89b3-fa3bd2ce9bfd',
        ];
    }

    #[Route(path: '/test', name: 'power_bi_report_test', methods: ['GET'])]
    #[Template('power_bi/test.html.twig')]
    public function test()
    {
        return [];
    }
}
