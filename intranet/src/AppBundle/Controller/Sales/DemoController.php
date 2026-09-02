<?php

declare(strict_types=1);

namespace AppBundle\Controller\Sales;

use ActivityBundle\Form\Type\CommentType;
use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Sales\DemoFilterType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Sales\Demo\DemoAddAirportType;
use AppBundle\Form\Type\Sales\Demo\DemoAddERType;
use AppBundle\Form\Type\Sales\Demo\DemoCloseType;
use AppBundle\Form\Type\Sales\Demo\DemoType;
use AppBundle\Form\Type\SimpleFileType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/sales/demos', defaults: ['alvest_module' => 'DEMO', 'moduleDomain' => 'demo'])]
class DemoController extends AbstractController
{
    final public const RESOURCE_URL = 'sales/demos';
    final public const ACTIVE = 'ACTIVE';
    final public const CANCELLED = 'CANCELLED';
    final public const searchItemsPerPage = 25;
    private readonly Client $client;
    private readonly FormFactoryInterface $formFactory;
    private readonly ViolationMapper $violationMapper;
    private readonly TranslatorInterface $translator;
    private readonly FileStreamedResponseFactory $fileStreamedResponseFactory;
    private readonly FileManager $fileManager;

    public function __construct(Client $client, FormFactoryInterface $formFactory, ViolationMapper $violationMapper, TranslatorInterface $translator, FileStreamedResponseFactory $fileStreamedResponseFactory, FileManager $fileManager)
    {
        $this->client = $client;
        $this->formFactory = $formFactory;
        $this->violationMapper = $violationMapper;
        $this->translator = $translator;
        $this->fileStreamedResponseFactory = $fileStreamedResponseFactory;
        $this->fileManager = $fileManager;
    }

    #[Route(path: '', name: 'demo_home', methods: ['GET|POST'])]
    #[Template('sales/demos/home.html.twig')]
    public function index(Request $request)
    {
        $parameters = [
            'itemsPerPage' => 10,
        ];
        $reportTitle = 'demo.last_demos';
        $searchTable = false;
        $formFilter = $this
            ->formFactory
            ->createNamed(
                '',
                DemoFilterType::class, [],
                [
                    'action' => $this->generateUrl('demo_home'),
                    'method' => 'GET',
                ]);

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $parameters = [
                'itemsPerPage' => 2000,
                'pagination' => false,
            ];
            $parameters = array_merge($parameters, $formFilter->getData());

            $reportTitle = 'demo.filtered';
            $searchTable = true;
        }

        if (isset($parameters['delinquent']) && !$parameters['delinquent']) {
            unset($parameters['delinquent']);
        }

        $demos = $this->client->findBy(self::RESOURCE_URL, $parameters, ['id' => 'desc']);

        // Search action
        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $this->client->get(\sprintf('sales/demos/%s', $id));

                return $this->redirectToRoute('demo_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', \sprintf('DEMO #%s does not exist', $id));
            }
        }

        foreach ($demos as $demo) {
            $demo['comment'] = 'CLOSED' === $demo['status'] ? $demo['closingComment'] : $demo['comment'];
        }

        return [
            'demos' => $demos,
            'reportTitle' => $reportTitle,
            'searchTable' => $searchTable,
            'formFilter' => $formFilter->createView(),
            'idSearchForm' => $idSearchForm->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'demo_show', methods: ['GET|POST'])]
    #[Template('sales/demos/show.html.twig')]
    public function showDemoDetail(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $demo, Request $request)
    {
        $demoClosed = false;

        if (\in_array($demo['status'], ['SUCCESSFUL', 'REJECTED', 'UNSUCCESSFUL', 'SUCCESSFUL_FUTURE_SALE'], true)) {
            $demoClosed = true;
        }

        $formEquipmentRecord = $this
            ->formFactory
            ->createNamed(
                'demo_er_form',
                DemoAddERType::class,
                [],
                [
                    'product' => $demo['product']['@id'],
                ]
            )
        ;

        $formAirport = $this
            ->formFactory
            ->createNamed(
                'demo_airport_form',
                DemoAddAirportType::class,
                $demo
            )
        ;

        $formComment = $this
            ->formFactory
            ->createNamed(
                'demo_comment',
                CommentType::class,
                [],
                [
                    'prefilled_comment' => $this->translator->trans('demo.prefilled_comment', [], 'demo'),
                ]
            )
        ;

        $formFiles = $this->formFactory->createNamed('demo_file', SimpleFileType::class);
        $formComment->handleRequest($request);
        if ($formComment->isSubmitted() && $formComment->isValid()) {
            try {
                $data = $formComment->getData();

                $payload = [
                    '@id' => $demo['@id'],
                    'comment' => $data['message'],
                ];

                $this->client->save('sales/demos', $payload);

                $this->addFlash(
                    'success',
                    $this->translator->trans('demo.comment.success', [], 'demo')
                );

                return $this->redirectToRoute('demo_show', ['id' => Iri::id($demo)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formComment);
            }
        }

        $formFiles->handleRequest($request);
        if ($formFiles->isSubmitted() && $formFiles->isValid()) {
            try {
                /** @var UploadedFile $file */
                $file = $formFiles->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $this->fileManager->uploadFile($demo, $file, self::RESOURCE_URL, $formFiles->get('description')->getData(), 'files');
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('demo.messages.success.file', [], 'demo')
                );

                return $this->redirectToRoute('demo_show', ['id' => Iri::id($demo)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formFiles);
            }
        }

        $formEquipmentRecord->handleRequest($request);
        if ($formEquipmentRecord->isSubmitted() && $formEquipmentRecord->isValid()) {
            try {
                $data = $formEquipmentRecord->getData();
                $this->client->save('sales/demos', [
                    '@id' => $demo->getIri(),
                    'equipmentRecord' => $data['equipmentRecord'],
                ]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('demo.add_er.success', [], 'demo')
                );

                return $this->redirectToRoute('demo_show', ['id' => Iri::id($demo)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formEquipmentRecord);
            }
        }

        $formAirport->handleRequest($request);
        if ($formAirport->isSubmitted() && $formAirport->isSubmitted()) {
            try {
                $data = $formAirport->getData();
                $this->client->save('sales/demos', [
                    '@id' => $demo->getIri(),
                    'airport' => $data['airport'],
                ]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('demo.add_airport.success', [], 'demo')
                );

                return $this->redirectToRoute('demo_show', ['id' => Iri::id($demo)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $formAirport);
            }
        }

        return [
            'demo' => $demo,
            'form_er' => $formEquipmentRecord->createView(),
            'form_files' => $formFiles->createView(),
            'form_comment' => $formComment->createView(),
            'form_airport' => $formAirport->createView(),
            'demoClosed' => $demoClosed,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'demo_edit', methods: ['GET|POST'])]
    #[Template('sales/demos/edit_demo.html.twig')]
    #[IsGranted(attribute: 'DEMO_EDIT_VOTER', subject: new Expression('args["demo"].getIri()'))]
    public function editDemo(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $demo)
    {
        $demo['comment'] = null;
        $authorizedFields = $this->client->get('/fields', ['query' => ['iri' => $demo['@id'], 'method' => 'PUT']]);
        $authorizedFields[] = 'status';
        $authorizedFields[] = 'comment';

        $form = $this
            ->formFactory
            ->createNamed(
                'demo_form',
                DemoType::class,
                $demo,
                [
                    'add' => false,
                    'authorizedFields' => $authorizedFields,
                    'product' => $demo['product']['@id'],
                ]
            )
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                unset($data['approvers'], $data['futureDemo']);
                if (null === $data['comment']) {
                    unset($data['comment']);
                }

                if (isset($data['status']) && $data['status'] !== $demo['status']) {
                    $this->client->put(\sprintf(self::RESOURCE_URL.'/%d/status', $demo->getIriId()), ['json' => ['status' => $data['status']]]);
                }

                $this->client->save('sales/demos', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('demo.edit.success', [], 'demo')
                );

                return $this->redirectToRoute('demo_show', ['id' => Iri::id($demo)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'demo' => $demo,
        ];
    }

    #[Route(path: '/add', name: 'demo_add', methods: ['GET|POST'])]
    #[Template('sales/demos/add_demo.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DEMO_CREATE') or is_granted('MOO_DEMO')"))]
    public function addDemo(Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed('demo_type_form', DemoType::class)
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->client->save('sales/demos', $data);

                $this->addFlash(
                    'success',
                    $this->translator->trans('demo.add.success', [], 'demo')
                );

                return $this->redirectToRoute('demo_home');
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/close_demo', name: 'sales_demos_close', methods: ['GET|POST'], defaults: ['label' => 'demo.close_demo'])]
    #[Template('sales/demos/close_demo.html.twig')]
    #[IsGranted(attribute: 'DEMO_STATUS_VOTER', subject: new Expression('args["demo"].getIri()'))]
    public function closeDemo(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $demo, Request $request)
    {
        $form = $this
            ->formFactory
            ->createNamed(
                'demo_form',
                DemoCloseType::class,
                $demo,
                [
                    'prefilled_data' => $this->translator->trans('demo.prefilled_closing_comment', [], 'demo'),
                ]
            )
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();

                $payload = [
                    '@id' => $data['@id'],
                    'closingComment' => $data['closingComment'],
                ];

                if (isset($data['futureDemo'])) {
                    $payload['futureDemo'] = $data['futureDemo'];
                }
                $this->client->save('sales/demos', $payload);

                $this->client->put(\sprintf(self::RESOURCE_URL.'/%d/status', $demo->getIriId()), ['json' => ['status' => $data['status']]]);

                $this->addFlash(
                    'success',
                    $this->translator->trans('demo.add_comment.success', [], 'demo')
                );

                return $this->redirectToRoute('demo_show', ['id' => Iri::id($demo)]);
            } catch (ClientException $e) {
                $this->violationMapper->mapToForm($e, $form);
            }
        }

        return [
            'demo' => $demo,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/demos/{demoId}/files/{id}/delete', name: 'sales_delete_demo_file', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DEMO_ADMIN') or is_granted('MOO_DEMO')"))]
    public function deleteFile(Request $request, #[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'id' => 'demoId'])] ApiData $demo, $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete_demo_file', $request->query->get('_token'))) {
            $this->addFlash('error', $this->translator->trans('files.delete_error', [], 'messages'));

            return $this->redirectToRoute('demo_show', ['id' => $demo->getIriId()]);
        }
        $this->fileManager->deleteFile($demo, self::RESOURCE_URL, \sprintf('files/%s', $id));

        return $this->redirectToRoute('demo_show', ['id' => $demo->getIriId()]);
    }

    #[Route(path: '/{demoId}/files/{id}', name: 'sales_demo_files_show', methods: 'GET')]
    public function showFile($demoId, $id)
    {
        return $this->fileStreamedResponseFactory->create(\sprintf('sales/demos/%s/files/%s', $demoId, $id));
    }

    #[Route(path: '/reports', name: 'sales_demos_reports', methods: ['GET'], defaults: ['label' => 'sidebar.common.reports', 'domain' => 'sidebar'])]
    #[Template('sales/demos/reports.html.twig')]
    public function showReports()
    {
        return [
            'demoBySSOReport' => $this->client->get('reports/resource=/sales/demos;x=sso.name;y=status'),
            'demoByFactoryReport' => $this->client->get('reports/resource=/sales/demos;x=factory.name;y=status'),
            'demoDelinquentByFactoryReport' => $this->client->get('reports/resource=/sales/demos;x=factory.name;y=delinquent'),
            'demoDelinquentBySSOReport' => $this->client->get('reports/resource=/sales/demos;x=sso.name;y=delinquent'),
            'demoBySSOFactory' => $this->client->get('reports/resource=/sales/demos;x=sso.name;y=factory.name'),
        ];
    }

    #[Route(path: '/{id}/delete', name: 'sales_demos_delete', methods: ['GET|DELETE'])]
    #[IsGranted('FEATURE_DEMO_DELETE')]
    public function deleteDemo(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $demo): RedirectResponse
    {
        try {
            $this->client->remove('sales/demos', $demo['id']);

            $this->addFlash(
                'success',
                $this->translator->trans('demo.delete.success', [], 'demo')
            );
        } catch (ClientException $e) {
            $this->addFlash(
                'error',
                $this->translator->trans('demo.delete.fail', [], 'demo')
            );
        }

        return $this->redirectToRoute('demo_home');
    }

    #[Route(path: '/{id}/move-to-active', name: 'sales_demos_active', methods: ['GET'])]
    #[IsGranted(attribute: 'DEMO_STATUS_VOTER', subject: new Expression('args["demo"].getIri()'))]
    public function moveToActive(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $demo): RedirectResponse
    {
        try {
            $this->client->put(\sprintf(self::RESOURCE_URL.'/%d/status', $demo->getIriId()), ['json' => ['status' => self::ACTIVE]]);
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);

            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->translator->trans('demo.manual_active_fail', [], 'demo'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('demo_show', ['id' => Iri::id($demo)]);
    }

    #[Route(path: '/{id}/move-to-cancel', name: 'sales_demos_cancel', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_DEMO_ADMIN') or is_granted('MOO_DEMO')"))]
    public function moveToCancelDemo(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $demo): RedirectResponse
    {
        try {
            $this->client->put(\sprintf(self::RESOURCE_URL.'/%d/status', $demo->getIriId()), ['json' => ['status' => self::CANCELLED]]);
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);

            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->translator->trans('demo.manual_active_fail', [], 'demo'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('demo_show', ['id' => Iri::id($demo)]);
    }

    #[Route(path: '/{id}/subscription', name: 'demo_subscription_home', methods: ['GET|POST'], defaults: ['label' => 'subscribers', 'domain' => 'messages'])]
    #[Template('sales/demos/subscription.html.twig')]
    public function subscription(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL])] ApiData $demo)
    {
        return ['demo' => $demo];
    }
}
