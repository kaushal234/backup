<?php

declare(strict_types=1);

namespace AppBundle\Controller\HumanResources;

use ApiBundle\Client;
use ApiBundle\Http\CsvStreamedResponseFactory;
use ApiBundle\Hydra\HydraCollection;
use ApiBundle\Iri\Iri;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Directory\PositionClassificationController;
use AppBundle\Filters\Type\HumanResources\EmployeeStaffing\EmployeeStaffingReportFilterType;
use AppBundle\Form\Type\Directory\Position\PositionClassificationReportBatchType;
use AppBundle\Form\Type\HumanResources\EmployeeStaffingDownloadFormType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: 'human-resources/employee-staffing', defaults: ['alvest_module' => 'ESM', 'moduleDomain' => 'human_resources_employee_staffing'])]
class EmployeeStaffingController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, TranslatorInterface::class, FormFactoryInterface::class, CsvStreamedResponseFactory::class]);
    }

    #[Route(path: '/report', name: 'human_resources_employee_staffing_home', methods: ['GET|POST'])]
    #[Template('human_resources/employee_staffing/report.html.twig')]
    #[IsGranted('FEATURE_EMPLOYEE_STAFFING_REPORT')]
    public function report(
        #[ApiValueResolverAttribute(parameters: ['filters' => ['order' => ['directHeadcount' => 'ASC', 'positionCategoryType.name' => 'ASC', 'name' => 'ASC']]])] HydraCollection $positionCategories,
        Request $request)
    {
        $filterForm = $this->container->get(FormFactoryInterface::class)->createNamed('', EmployeeStaffingReportFilterType::class);

        $options = [];
        $entity = $request->query->get('entity');
        $filterForm->handleRequest($request);

        if ($filterForm->get('submitRegionalResultsReview')->isClicked()) {
            return $this->redirectToRoute('regional_results_review', $request->query->all());
        }

        if (($filterForm->isSubmitted() && ('' !== $entity = $filterForm->getData()['entity'])) || null !== $entity) {
            $options['entity'] = $entity;
        }

        // this variable will determine if the report should display the fields or not
        $editable = $this->isGranted('POSITION_CLASSIFICATION_WRITE_VOTER', $entity);

        if ($filterForm->isSubmitted() && null !== $snapshot = $filterForm->getData()['snapshot']) {
            $options['snapshotDate'] = $snapshot;
            $editable = false;
        }

        $client = $this->container->get(Client::class);

        try {
            $report = $client->get(
                '/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name',
                ['query' => ['options' => $options]]
            );
        } catch (ClientException $exception) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('employee_staffing.message.not_found', [], 'employee_staffing'));

            return $this->redirectToRoute('human_resources_employee_staffing_home');
        }

        $downloadPayload = [];
        $people = [];
        if ($filterForm->isSubmitted() && (null !== $filterForm->getData()['categorized'] || null !== $filterForm->getData()['contractType'] || null !== $filterForm->getData()['positionCategory'])) {
            $payload = [
                'disabled' => false,
                'normalization_groups' => ['group_member'],
            ];
            foreach (['categorized', 'contractType', 'positionCategory'] as $key) {
                if (null !== ($value = $filterForm->getData()[$key])) {
                    $payload[$key] = $value;
                }
            }

            switch ($report['metadata']['scope']) {
                case 'BusinessUnit':
                    $filter = 'businessUnit';
                    break;
                case 'Region':
                    $filter = 'businessUnit.region';
                    break;
                case 'SubDivision':
                    $filter = 'businessUnit.region.subDivision';
                    break;
                case 'Division':
                    $filter = 'businessUnit.region.subDivision.division';
                    break;
                default:
                    $filter = null;
            }

            if (null !== $filter && null !== $entity) {
                $payload[$filter] = $entity;
            }

            if ($filterForm->getClickedButton() && 'download' === $filterForm->getClickedButton()->getName()) {
                $payload['itemsPerPage'] = 5000;

                return $this->container->get(CsvStreamedResponseFactory::class)->create('people/download_directory', $payload, 'esm.csv');
            }

            try {
                $people = $client->get('/people', [
                    'query' => $payload,
                ]);
            } catch (ClientException $e) {
                $error = json_decode($e->getResponse()->getContent(false), true);
                $this->addFlash('warning', $error['hydra:description']);
            }
            $downloadPayload = $payload;
        }

        $downloadForm = $this->createForm(EmployeeStaffingDownloadFormType::class, null, [
            'payload' => $downloadPayload,
        ]);

        $downloadForm->handleRequest($request);

        if ($downloadForm->isSubmitted() && $downloadForm->isValid()) {
            $payloadJson = (string) $downloadForm->get('payload')->getData();
            $payload = json_decode($payloadJson, true, 512, \JSON_THROW_ON_ERROR);
            $payload['itemsPerPage'] = 5000;

            return $this->container->get(CsvStreamedResponseFactory::class)->create('people/download_directory', $payload, 'esm.csv');
        }

        $form = null;
        if ($editable && null !== $entity) {
            $positionClassifications = $client->get(PositionClassificationController::RESOURCE_URL, [
                'query' => [
                    'businessUnit' => $entity,
                    'positionCategory.divisions.subDivisions.regions.businessUnits' => $entity,
                ],
            ]);

            $indexedPositionClassifications['positionClassifications'] = [];
            foreach ($positionCategories->getSimpleArrayCopy() as $positionCategory) {
                $positionCategoryId = $positionCategory['id'];
                $indexedPositionClassifications['positionClassifications'][$positionCategoryId] = ['positionCategory' => $positionCategory['@id']];
                foreach ($positionClassifications['hydra:member'] as $positionClassification) {
                    if ($positionCategory['name'] === $positionClassification['positionCategory']['name']) {
                        $indexedPositionClassifications['positionClassifications'][$positionCategoryId] += $positionClassification;
                    }
                }
            }

            $form = $this->createForm(PositionClassificationReportBatchType::class, $indexedPositionClassifications);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $data = $form->getData();
                $success = false;
                foreach ($data['positionClassifications'] as $positionClassificationSubmitted) {
                    $indexedPositionClassification = $indexedPositionClassifications['positionClassifications'][Iri::id($positionClassificationSubmitted['positionCategory'])];
                    if ($positionClassificationSubmitted['budget'] === (float) ($indexedPositionClassification['budget'] ?? null)
                        && $positionClassificationSubmitted['correction'] === (float) ($indexedPositionClassification['correction'] ?? null)
                        && $positionClassificationSubmitted['reforecast'] === (float) ($indexedPositionClassification['reforecast'] ?? null)
                        && $positionClassificationSubmitted['comment'] === ($indexedPositionClassification['comment'] ?? null)
                    ) {
                        continue;
                    }

                    $payload = isset($positionClassificationSubmitted['@id']) ? ['@id' => $positionClassificationSubmitted['@id']] : ['positionCategory' => $indexedPositionClassification['positionCategory']];
                    $payload += [
                        'budget' => $positionClassificationSubmitted['budget'],
                        'comment' => $positionClassificationSubmitted['comment'],
                        'reforecast' => $positionClassificationSubmitted['reforecast'],
                        'correction' => $positionClassificationSubmitted['correction'],
                        'businessUnit' => $entity,
                    ];

                    try {
                        $client->save(PositionClassificationController::RESOURCE_URL, $payload);
                        $success = true;
                    } catch (ClientException $e) {
                        $errors = json_decode($e->getResponse()->getContent(false), true);
                        foreach ($errors['violations'] ?? [] as $error) {
                            $this->addFlash(
                                'warning',
                                $error['message']
                            );
                        }
                    }
                }

                if ($success) {
                    $this->addFlash(
                        'success',
                        $this->container->get(TranslatorInterface::class)->trans('directory.position_classifications.edit.success', [], 'directory')
                    );
                }

                return $this->redirectToRoute('human_resources_employee_staffing_home', ['entity' => $entity]);
            }
        }

        return [
            'editable' => $editable,
            'entity' => $entity,
            'filterForm' => $filterForm->createView(),
            'report' => $report,
            'positionCategories' => $positionCategories,
            'form' => null !== $form ? $form->createView() : null,
            'people' => $people['hydra:member'] ?? [],
            'downloadForm' => $downloadForm->createView(),
        ];
    }

    #[Route(path: '/regional-results-review', name: 'regional_results_review')]
    #[Template('human_resources/employee_staffing/regional_results_review.html.twig')]
    #[IsGranted('FEATURE_EMPLOYEE_STAFFING_REPORT')]
    public function regionalResultsReview(#[ApiValueResolverAttribute(parameters: ['filters' => ['order' => ['directHeadcount' => 'ASC', 'positionCategoryType.name' => 'ASC', 'name' => 'ASC']]])] HydraCollection $positionCategories, Request $request)
    {
        $filterForm = $this->container->get(FormFactoryInterface::class)->createNamed('', EmployeeStaffingReportFilterType::class);

        $options = [];
        $entity = $request->query->get('entity');
        $filterForm->handleRequest($request);

        if ($filterForm->get('submit')->isClicked()) {
            return $this->redirectToRoute('human_resources_employee_staffing_home', $request->query->all());
        }

        if (($filterForm->isSubmitted() && ('' !== $entity = $filterForm->getData()['entity'])) || null !== $entity) {
            $options['entity'] = $entity;
        }

        // this variable will determine if the report should display the fields or not
        $editable = $this->isGranted('POSITION_CLASSIFICATION_WRITE_VOTER', $entity);

        if ($filterForm->isSubmitted() && null !== $snapshot = $filterForm->getData()['snapshot']) {
            $options['snapshotDate'] = $snapshot;
            $editable = false;
        }

        $client = $this->container->get(Client::class);

        try {
            $report = $client->get(
                '/reports/resource=/people;x=businessUnit.positionClassifications.positionCategory.name;y=contractType.name',
                ['query' => ['options' => $options]]
            );
        } catch (ClientException $exception) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('employee_staffing.message.not_found', [], 'employee_staffing'));

            return $this->redirectToRoute('human_resources_employee_staffing_home');
        }

        $report['xTotals'] = array_filter($report['xTotals']);

        $filteredPositionCategories = [];
        $directCategoryTypesAlreadySet = [];
        $indirectTotal = 0;
        $directTotal = 0;

        foreach ($positionCategories as $positionCategory) {
            $categoryName = $positionCategory['name'];
            $categoryTypeName = $positionCategory['positionCategoryType']['name'];

            if (!isset($report['xTotals'][$categoryName])) {
                continue;
            }

            if (false === $positionCategory['directHeadcount']) {
                $indirectTotal += $report['xTotals'][$categoryName];
            }

            if (true === $positionCategory['directHeadcount']) {
                $directTotal += $report['xTotals'][$categoryName];
                if (!\in_array($categoryTypeName, $directCategoryTypesAlreadySet, true)) {
                    $report['xTotals'][$categoryTypeName] = 0;
                    $report['metadata']['classifications'][$categoryTypeName] = [
                        'correction' => 0,
                        'comments' => [],
                        'previousYearCorrectedTotal' => 0,
                        'budget' => 0,
                        'reforecast' => 0,
                        'iris' => [],
                    ];
                    $directCategoryTypesAlreadySet[] = $categoryTypeName;
                }

                $report['xTotals'][$categoryTypeName] += $report['xTotals'][$categoryName];

                $report['metadata']['classifications'][$categoryTypeName]['correction'] += $report['metadata']['classifications'][$categoryName]['correction'];

                $sourceComments = $report['metadata']['classifications'][$categoryName]['comments'] ?? [];
                $report['metadata']['classifications'][$categoryTypeName]['comments'] = array_merge(
                    $report['metadata']['classifications'][$categoryTypeName]['comments'],
                    $sourceComments
                );

                $report['metadata']['classifications'][$categoryTypeName]['previousYearCorrectedTotal'] += $report['metadata']['classifications'][$categoryName]['previousYearCorrectedTotal'];

                $report['metadata']['classifications'][$categoryTypeName]['budget'] += $report['metadata']['classifications'][$categoryName]['budget'];

                $report['metadata']['classifications'][$categoryTypeName]['reforecast'] += $report['metadata']['classifications'][$categoryName]['reforecast'];

                $sourceIris = $report['metadata']['classifications'][$categoryName]['iris'] ?? [];
                $report['metadata']['classifications'][$categoryTypeName]['iris'] = array_merge(
                    $report['metadata']['classifications'][$categoryTypeName]['iris'],
                    $sourceIris
                );

                unset($report['xTotals'][$categoryName], $report['metadata']['classifications'][$categoryName]);

                continue;
            }
            $filteredPositionCategories[] = $positionCategory;
        }

        $report = array_merge($report, ['indirectTotal' => $indirectTotal], ['directTotal' => $directTotal]);
        $downloadPayload = [];
        $people = [];
        if ($filterForm->isSubmitted() && (null !== $filterForm->getData()['categorized'] || null !== $filterForm->getData()['contractType'] || null !== $filterForm->getData()['positionCategory'])) {
            $payload = [
                'disabled' => false,
                'normalization_groups' => ['group_member'],
            ];
            foreach (['categorized', 'contractType', 'positionCategory'] as $key) {
                if (null !== ($value = $filterForm->getData()[$key])) {
                    $payload[$key] = $value;
                }
            }

            switch ($report['metadata']['scope']) {
                case 'BusinessUnit':
                    $filter = 'businessUnit';
                    break;
                case 'Region':
                    $filter = 'businessUnit.region';
                    break;
                case 'SubDivision':
                    $filter = 'businessUnit.region.subDivision';
                    break;
                case 'Division':
                    $filter = 'businessUnit.region.subDivision.division';
                    break;
                default:
                    $filter = null;
            }

            if (null !== $filter && null !== $entity) {
                $payload[$filter] = $entity;
            }

            if ($filterForm->getClickedButton() && 'download' === $filterForm->getClickedButton()->getName()) {
                $payload['itemsPerPage'] = 5000;

                return $this->container->get(CsvStreamedResponseFactory::class)->create('people/download_directory', $payload, 'esm.csv');
            }

            try {
                $people = $client->get('/people', [
                    'query' => $payload,
                ]);
            } catch (ClientException $e) {
                $error = json_decode($e->getResponse()->getContent(false), true);
                $this->addFlash('warning', $error['hydra:description']);
            }
            $downloadPayload = $payload;
        }

        $downloadForm = $this->createForm(EmployeeStaffingDownloadFormType::class, null, [
            'payload' => $downloadPayload,
        ]);

        $downloadForm->handleRequest($request);

        if ($downloadForm->isSubmitted() && $downloadForm->isValid()) {
            $payloadJson = (string) $downloadForm->get('payload')->getData();
            $payload = json_decode($payloadJson, true, 512, \JSON_THROW_ON_ERROR);
            $payload['itemsPerPage'] = 5000;

            return $this->container->get(CsvStreamedResponseFactory::class)->create('people/download_directory', $payload, 'esm.csv');
        }

        return [
            'entity' => $entity,
            'filterForm' => $filterForm->createView(),
            'report' => $report,
            'positionCategories' => $filteredPositionCategories,
            'directCategoryTypes' => $directCategoryTypesAlreadySet,
            'people' => $people['hydra:member'] ?? [],
            'downloadForm' => $downloadForm->createView(),
        ];
    }
}
