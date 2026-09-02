<?php

declare(strict_types=1);

namespace AppBundle\Controller\Finance;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Form\Type\Finance\ManufacturingMarginType;
use AppBundle\Form\Type\Finance\ManufacturingReportDownloadType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\LegacyIdSearchType;
use AppBundle\Form\Type\SimpleFileType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/finance/manufacturing-margins', defaults: ['alvest_module' => 'RRR', 'moduleDomain' => 'manufacturing_margin'])]
class ManufacturingMarginController extends AbstractController
{
    final public const SYNTHESIS_RESOURCE_URL = 'finance/manufacturing_margin_syntheses';
    final public const RESOURCE_URL = 'finance/manufacturing_margins';
    final public const TRANSLATION_DOMAIN = 'manufacturing_margin';
    final public const VIEW_ROLES = ['ROLE_FC', 'ROLE_MPE', 'ROLE_PS', 'ROLE_PSM'];

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [Client::class, ViolationMapper::class, TranslatorInterface::class, FileStreamedResponseFactory::class]);
    }

    #[Route(path: '', name: 'manufacturing_margin_home')]
    #[Template('finance/manufacturing-margin/home.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_FULL') or is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_LOCATION')"))]
    public function home(Request $request)
    {
        // Search action
        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $legacyIdSearchForm = $this->createForm(LegacyIdSearchType::class, null, [
            'legacy_id_label' => false,
            'legacy_id_placeholder' => 'By Legacy ID',
        ]);

        foreach (['id' => $idSearchForm, 'legacyId' => $legacyIdSearchForm] as $property => $form) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $id = $form->get($property)->getData();
                try {
                    $this->container->get(Client::class)->get(\sprintf('%s/%s', self::RESOURCE_URL, $id));

                    return $this->redirectToRoute('manufacturing_margin_show', ['id' => $id]);
                } catch (ClientException $e) {
                    $this->addFlash('error', \sprintf('Record #%s does not exist', $id));
                }
            }
        }

        return [
            'idSearchForm' => $idSearchForm->createView(),
            'legacyIdSearchForm' => $legacyIdSearchForm->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'manufacturing_margin_show', methods: ['GET'])]
    #[Template('finance/manufacturing-margin/show.html.twig')]
    #[IsGranted(attribute: 'MANUFACTURING_MARGIN_VIEW_VOTER', subject: new Expression('args["manufacturingMargin"].getIri()'))]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manufacturingMargin)
    {
        return ['manufacturingMargin' => $manufacturingMargin];
    }

    #[Route(path: '/{id}/edit', name: 'manufacturing_margin_edit', methods: ['GET|POST'])]
    #[Template('finance/manufacturing-margin/edit.html.twig')]
    #[IsGranted(attribute: 'MANUFACTURING_MARGIN_EDIT_VOTER', subject: new Expression('args["manufacturingMargin"].getIri()'))]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manufacturingMargin, Request $request)
    {
        $form = $this->container->get('form.factory')->createNamed('form', ManufacturingMarginType::class, $manufacturingMargin);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('manufacturing_margin.edit.success', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('manufacturing_margin_show', ['id' => Iri::id($manufacturingMargin)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'manufacturingMargin' => $manufacturingMargin,
        ];
    }

    #[Route(path: '/add', name: 'manufacturing_margin_add', methods: ['GET|POST'])]
    #[Template('finance/manufacturing-margin/add.html.twig')]
    #[IsGranted('FEATURE_MANUFACTURING_MARGIN_CREATE')]
    public function addManufacturingMargin(Request $request)
    {
        $form = $this->container->get('form.factory')->createNamed('form', ManufacturingMarginType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $manufacturingMargin = $this->container->get(Client::class)->save(self::RESOURCE_URL, $form->getData());

                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('manufacturing_margin.add.success', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('manufacturing_margin_show', ['id' => Iri::id($manufacturingMargin)]);
            } catch (ClientException $e) {
                $errorDescription = json_decode($e->getResponse()->getContent(false), true);

                $this->addFlash('error', $errorDescription['hydra:description']);
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'manufacturing_margin_delete', methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_MANUFACTURING_MARGIN_DELETE')]
    public function deleteManufacturingMargin(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $manufacturingMargin): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::RESOURCE_URL, $manufacturingMargin['id']);

            $this->addFlash(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('manufacturing_margin.delete.success', [], self::TRANSLATION_DOMAIN)
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('manufacturing_margin.delete.fail', [], self::TRANSLATION_DOMAIN)
            );
        }

        return $this->redirectToRoute('manufacturing_margin_home');
    }

    #[Route(path: '/reports', name: 'manufacturing_margin_reports', methods: ['GET|POST'])]
    #[Template('finance/manufacturing-margin/reports.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_FULL') or is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_LOCATION')"))]
    public function report(Request $request)
    {
        $filters = $this->getLocationFilters();
        $form = $this
            ->container
            ->get('form.factory')
            ->createNamed(
                'form',
                ManufacturingReportDownloadType::class,
                [],
                ['filters' => $filters, 'export' => false]
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $date = new \DateTime($data['date']);

                $factory = $this->container->get(Client::class)->find('locations', Iri::id($data['factory']));

                return [
                    'form' => $form->createView(),
                    'submitted' => true,
                    'factory' => $factory,
                    'year' => $date->format('Y'),
                    'month' => $date->format('n'),
                ];
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'submitted' => false,
        ];
    }

    #[Route(path: '/upload', name: 'manufacturing_margin_upload', methods: ['GET|POST'])]
    #[Template('finance/manufacturing-margin/upload.html.twig')]
    #[IsGranted('FEATURE_MANUFACTURING_MARGIN_CREATE')]
    public function upload(Request $request)
    {
        $form = $this->container->get('form.factory')->createNamed('file', SimpleFileType::class, [], ['only_file' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $form->get('file')->getData();

                $multiPart['file'] = DataPart::fromPath($file->getPathname(), $file->getClientOriginalName());
                $formData = new FormDataPart($multiPart);

                $this->container->get(Client::class)->post(\sprintf('%s/import_file', self::RESOURCE_URL), [
                    'headers' => $formData->getPreparedHeaders()->toArray(),
                    'body' => $formData->bodyToIterable(),
                ]);
                $this->addFlash(
                    'success',
                    $this->container->get(TranslatorInterface::class)->trans('manufacturing_margin.file.success', [], self::TRANSLATION_DOMAIN)
                );

                return $this->redirectToRoute('manufacturing_margin_home');
            } catch (ClientException $e) {
                $errors = $e->getResponse()->toArray(false);
                $invalidLines = [];

                foreach ($errors['violations'] as $error) {
                    $matches = [];
                    if (!preg_match_all('/^margins\[(?P<id>.+)]\.(?P<property>.+)/', (string) $error['propertyPath'], $matches, \PREG_SET_ORDER)) {
                        continue;
                    }
                    $invalidLines[] = [
                        // +2 because line 1 of file is header
                        'line' => (int) $matches[0]['id'] + 2,
                        'property' => $matches[0]['property'],
                        'message' => $error['message'],
                    ];
                }

                $this->addFlash(
                    'warning',
                    $this->container->get(TranslatorInterface::class)->trans('manufacturing_margin.file.error_message', [], self::TRANSLATION_DOMAIN)
                );

                return [
                    'form' => $form->createView(),
                    'errors' => true,
                    'invalidLines' => $invalidLines,
                ];
            }
        }

        return [
            'form' => $form->createView(),
            'errors' => false,
        ];
    }

    #[Route(path: '/download-by-er', name: 'manufacturing_margin_download_by_er', methods: ['GET|POST'])]
    #[Template('finance/manufacturing-margin/download.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_FULL') or is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_LOCATION')"))]
    public function downloadByER(Request $request)
    {
        $filters = $this->getLocationFilters();
        $form = $this->container->get('form.factory')->createNamed('form', ManufacturingReportDownloadType::class, [], ['filters' => $filters, 'full_possible' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $parameters = [
                    'equipmentRecord.manufacturerLocation' => $data['factory'],
                    'exportedAt' => [
                        'after' => (new \DateTime($data['from']))->modify('first day of this month')->format('Y-m-d'),
                        'before' => (new \DateTime($data['to']))->modify('last day of this month')->format('Y-m-d'),
                    ],
                    'pagination' => false,
                    'columns' => 'exportedAt,equipmentRecord.serialNumber,equipmentRecord.product.financeFamily,equipmentRecord.model,equipmentRecord.emissionRating,sol.customerUser,currency,factoryRevenue,factoryDiscount,actualDirectMarginPercentage,estDirMarginPer,modelBaseHours,industrialIncorporationParameter,optionConfigurationParameterHours,unitAllocatedHours,actualHours,factoryStandardEfficiency',
                ];

                if (true === $data['full']) {
                    $parameters['columns'] = 'exportedAt,equipmentRecord.serialNumber,equipmentRecord.product.financeFamily,equipmentRecord.model,equipmentRecord.emissionRating,sol.customerUser,currency,factoryRevenue,factoryDiscount,actualDirectMarginPercentage,estDirMarginPer,modelBaseHours,industrialIncorporationParameter,optionConfigurationParameterHours,unitAllocatedHours,actualHours,factoryStandardEfficiency,solId,equipmentRecord.manufacturerLocation,sso,equipmentRecord.type,standardDirectMarginPercentage,standardMaterialCost,actualMaterialCost,standardOtherDirectCost,actualOtherDirectCost,targetHours,standardHours,factoryStandardEfficiencyBudget';
                }

                return $this->container->get(FileStreamedResponseFactory::class)->create(
                    self::RESOURCE_URL,
                    ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
                    'restitution_by_er.xlsx'
                );
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'title' => $this->container->get(TranslatorInterface::class)->trans('manufacturing_margin.report_by_er', [], 'manufacturing_margin'),
            'route' => 'manufacturing_margin_download_by_er',
        ];
    }

    #[Route(path: '/download-by-finance-family', name: 'manufacturing_margin_download_by_finance_family', methods: ['GET|POST'])]
    #[Template('finance/manufacturing-margin/download.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_FULL') or is_granted('FEATURE_MANUFACTURING_MARGIN_VIEW_LOCATION')"))]
    public function downloadByFinanceFamily(Request $request)
    {
        $filters = $this->getLocationFilters();
        $form = $this->container->get('form.factory')->createNamed('form', ManufacturingReportDownloadType::class, [], ['filters' => $filters]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $parameters = [
                    'factory' => $data['factory'],
                    'exportDate' => [
                        'after' => $data['from'],
                        'before' => $data['to'],
                    ],
                    'itemsPerPage' => 1000,
                    'pagination' => false,
                ];

                return $this->container->get(FileStreamedResponseFactory::class)->create(
                    self::SYNTHESIS_RESOURCE_URL,
                    ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
                    'restitution_by_finance_family.xlsx',
                );
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'title' => $this->container->get(TranslatorInterface::class)->trans('manufacturing_margin.report_by_finance_family', [], 'manufacturing_margin'),
            'route' => 'manufacturing_margin_download_by_finance_family',
        ];
    }

    private function getLocationFilters()
    {
        $filters = ['capability.factory' => true];
        if (!$this->isGranted('ACL_SUPERUSER') && !$this->isGranted('ACL_ROLE_CFO')) {
            $client = $this->container->get(Client::class);
            $me = $client->get('me');
            $acls = $client->findBy('acls', ['user' => $me['@id']], [], ['raw_results' => true]);

            foreach ($acls['hydra:member'] as $acl) {
                if (\in_array($acl['group']['name'], self::VIEW_ROLES, true)) {
                    $filters['name'][] = isset($acl['location']) ? $acl['location']['name'] : null;
                }
            }
        }

        return $filters;
    }
}
