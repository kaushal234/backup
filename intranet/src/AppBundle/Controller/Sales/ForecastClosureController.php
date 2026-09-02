<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Chart\ChartBuilderFactory;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Sales\ForecastClosureDataTableType;
use AppBundle\Form\Type\Sales\ForecastClosure\ForecastClosureType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/forecast-closures', defaults: ['alvest_module' => 'FCR', 'moduleDomain' => 'forecast_closures'])]
class ForecastClosureController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    final public const RESOURCE_URL = 'sales/forecast_closures';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            ChartBuilderFactory::class,
            TranslatorInterface::class,
            ViolationMapper::class,
            FileManager::class,
            FileStreamedResponseFactory::class,
        ]);
    }

    #[Route(path: '', name: 'forecast_closures_home', methods: ['GET', 'POST'])]
    #[Template('sales/forecast_closures/list.html.twig')]
    public function list(Request $request)
    {
        $datatable = $this->createDataTable(ForecastClosureDataTableType::class, ForecastClosureDataTableType::RESOURCE);
        $datatable->handleRequest($request);

        if ($datatable->isExporting() && $datatable->getQuery() instanceof ApiProxyQuery) {
            return $datatable->getQuery()->export();
        }
        if ($datatable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($datatable);
        }

        /** @var ApiProxyQuery $query */
        $query = $datatable->getQuery();
        $filters = $query->getFilters();

        $reportPie = $this->container->get(Client::class)->get('reports/resource=/sales/forecast_closures;x=reason;y=', ['query' => ['options' => $filters]]);

        $chartBuilder = $this->container->get(ChartBuilderFactory::class)
            ->getPieChartBuilder()
            ->addPlotOptions([
                'startAngle' => -90,
                'endAngle' => 90,
                'center' => ['50%', '75%'],
            ])
            ->setTitle($this->container->get(TranslatorInterface::class)->trans('forecast_closures.report', [], 'forecast_closures'))
            ->disableLegend()
        ;

        foreach ($reportPie['xTotals'] as $key => $value) {
            $chartBuilder->addPlot(
                'Value',
                $key,
                $value,
                [],
                ['name' => $key]
            );
        }

        return [
            'chart' => $chartBuilder->buildConfig(),
            'forecastClosureDatatable' => $datatable->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'forecast_closures_show')]
    #[Template('sales/forecast_closures/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $forecastClosure, Request $request)
    {
        $competitorPricings = $this->container->get(Client::class)->findBy('sales/competitor_pricings', ['forecastClosure.salesForecast' => $forecastClosure['salesForecast']['@id']]);

        $form = $this->container->get('form.factory')->createNamed('forecastClosure', SimpleFileType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->container->get(FileManager::class)->uploadFile($forecastClosure, $file, self::RESOURCE_URL, $form->get('description')->getData(), 'files');
                }

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('customers.messages.success.file', [], 'sales_customers')
                );

                return $this->redirectToRoute('forecast_closures_show', ['id' => $forecastClosure->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'competitorPricings' => $competitorPricings,
            'forecastClosure' => $forecastClosure,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'forecast_closures_edit', methods: ['GET', 'POST'])]
    #[Template('sales/forecast_closures/edit.html.twig')]
    public function edit($id, Request $request)
    {
        $iri = \sprintf('/sales/forecast_closures/%s', $id);

        if (!$this->container->get('security.authorization_checker')->isGranted('FORECAST_CLOSURE_WRITE_VOTER', $iri)) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('forecast_closures.errors.not_allowed_edit', [], 'forecast_closures'));

            return $this->redirectToRoute('forecast_closures_home');
        }

        $forecastClosure = $this->container->get(Client::class)->get($iri);
        $salesForecast = $this->container->get(Client::class)->get($forecastClosure['salesForecast']['@id']);

        $forecastClosureForm = $this
            ->container->get('form.factory')
            ->createNamed(
                '',
                ForecastClosureType::class,
                $forecastClosure,
                [
                    'sales_forecast' => $salesForecast,
                ]
            )
        ;

        $forecastClosureForm->handleRequest($request);
        if ($forecastClosureForm->isSubmitted() && $forecastClosureForm->isValid()) {
            try {
                $data = $forecastClosureForm->getData();

                $data['forecastClosure'] = $forecastClosure['@id'];

                $data['salesForecast'] = $data['salesForecast']['@id'];

                $data['competitor'] = '' !== $data['competitor'] ? $data['competitor'] : null;

                $this->container->get(Client::class)->save(self::RESOURCE_URL, $data);

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('forecast_closures.messages.success_edit', [], 'forecast_closures')
                );

                return $this->redirectToRoute('forecast_closures_show', ['id' => Iri::id($forecastClosure)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $forecastClosureForm);
            }
        }

        return [
            'form' => $forecastClosureForm->createView(),
            'forecastClosure' => $forecastClosure,
        ];
    }

    #[Route(path: '/{id}/delete', name: 'forecast_closure_delete', methods: ['GET'])]
    public function delete(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $forecastClosure): RedirectResponse
    {
        if (!$this->container->get('security.authorization_checker')->isGranted('FEATURE_SALES_FORECAST_ADMIN_EDIT', $forecastClosure['salesForecast']['@id']) && !$this->container->get('security.authorization_checker')->isGranted('MOO_FCR')) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('forecast_closures.messages.not_allowed_delete', [], 'forecast_closures'));

            return $this->redirectToRoute('forecast_closures_show', ['id' => $forecastClosure->getIriId()]);
        }

        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $forecastClosure->getIriId());
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('forecast_closures.messages.success_delete', [], 'forecast_closures'));
        } catch (ClientException $e) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('forecast_closures.messages.error_delete', [], 'forecast_closures'));
        }

        return $this->redirectToRoute('forecast_closures_home');
    }

    #[Route(path: '/{forecastClosureId}/files/{id}/delete', name: 'forecast_closure_delete_file', methods: ['GET'])]
    #[IsGranted(attribute: 'FORECAST_CLOSURE_WRITE_VOTER', subject: new Expression('args["forecastClosure"].getIri()'))]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'forecastClosureId'])] ApiData $forecastClosure, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_forecast_closure_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('forecast_closures_show', ['id' => $forecastClosure['id']]);
        }
        $this->container->get(FileManager::class)->deleteFile($forecastClosure, self::RESOURCE_URL, \sprintf('files/%s', $id));

        return $this->redirectToRoute('forecast_closures_show', ['id' => $forecastClosure['id']]);
    }

    #[Route(path: '/{forecastClosureId}/files/{id}', name: 'forecast_closure_files_show', methods: 'GET')]
    public function showFile($forecastClosureId, $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('sales/forecast_closures/%s/files/%s', $forecastClosureId, $id));
    }
}
