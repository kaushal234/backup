<?php

declare(strict_types=1);

namespace AppBundle\Controller\Quality;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Filters\Type\Quality\Derogation\DerogationFilterType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Quality\Crab\DecisionDerogationType;
use AppBundle\Form\Type\Quality\Crab\DerogationEditType;
use AppBundle\Form\Type\Quality\Crab\DerogationRescheduleType;
use AppBundle\Form\Type\Quality\Crab\DerogationType;
use AppBundle\Manager\FileManager;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(defaults: ['alvest_module' => 'CRAB', 'moduleDomain' => 'crab'])]
class DerogationController extends AbstractController
{
    public const DEROGATION_URL = 'quality/derogations';
    public const CRAB_URL = 'quality/crabs';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [FileStreamedResponseFactory::class, Client::class, TranslatorInterface::class, FormFactoryInterface::class, ViolationMapper::class, FileManager::class]);
    }

    #[Route(path: '/quality/derogations', name: 'derogation_home', defaults: ['alvest_module' => 'DEROGATION', 'moduleDomain' => 'derogation'], methods: ['GET|POST'])]
    #[Template('quality/derogation/home.html.twig')]
    public function home(Request $request)
    {
        $client = $this->container->get(Client::class);
        $parameters = ['itemsPerPage' => 10];
        $filtered = false;

        $idSearchForm = $this->createForm(IdSearchType::class, null, ['id_label' => false, 'id_placeholder' => 'By ID'])->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::DEROGATION_URL, $id));

                return $this->redirectToRoute('derogation_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('crab.errors.derogation_not_exist', ['%id%' => $id], 'crab'));
            }
        }

        $formFilter = $this->container->get(FormFactoryInterface::class)->createNamed('', DerogationFilterType::class, [],
            [
                'action' => $this->generateUrl('derogation_home'),
                'method' => Request::METHOD_GET,
            ]
        );

        $formFilter->handleRequest($request);
        if ($formFilter->isSubmitted() && $formFilter->isValid()) {
            $filtered = true;
            $data = $formFilter->getData();
            $parameters = array_merge($parameters, $data);
            $parameters['itemsPerPage'] = 500;
            $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['id' => 'asc'];

            if ($formFilter->getClickedButton() && 'download' === $formFilter->getClickedButton()->getName()) {
                $parameters = array_merge($parameters, $data);
                $parameters['itemsPerPage'] = 20000;
                $parameters['order'] = [] !== $request->query->all('order') ? $request->query->all('order') : ['id' => 'desc'];
                $parameters['columns'] = 'id,status,description,shortDescription,assignee,assignor,comment,dueDate,closedAt,closedBy';

                return $this->container->get(FileStreamedResponseFactory::class)->create(
                    self::DEROGATION_URL,
                    ['query' => $parameters, 'headers' => ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
                    'derogation.xlsx'
                );
            }
        }

        return [
            'filtered' => $filtered,
            'derogationReportTitle' => $filtered ? 'crab.derogation.title.filtered' : 'crab.derogation.title.last_ones',
            'form_id' => $idSearchForm->createView(),
            'form_filter' => $formFilter->createView(),
            'derogationsCollection' => $client->findBy(self::DEROGATION_URL, $parameters, ['id' => 'desc']),
        ];
    }

    #[Route(path: '/quality/crabs/{id}/derogations/add', name: 'crab_derogation_add', defaults: ['label' => 'crab.title.add_derogation'], methods: 'GET|POST')]
    #[Template('quality/derogation/write_derogation.html.twig')]
    #[IsGranted('FEATURE_DEROGATION_CREATE')]
    public function addDerogation(#[ApiValueResolverAttribute(parameters: ['resource' => self::CRAB_URL])] ApiData $crab, Request $request)
    {
        $client = $this->container->get(Client::class);
        $form = $this->createForm(DerogationType::class, [], ['add' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $payload = array_merge($form->getData(), ['crabs' => [$crab->getIri()]]);
            try {
                $derogation = $client->save(self::DEROGATION_URL, $payload);
                $file = $form->get('file')->getData();
                if (null !== $file) {
                    $this->container->get(FileManager::class)->uploadFile(
                        $derogation,
                        $file,
                        self::DEROGATION_URL,
                        null,
                        'files',
                        false,
                        true
                    );
                }
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.success.add_derogation', [], 'crab'));

                return $this->redirectToRoute('crab_show', ['id' => $crab->getIriId()]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'crab' => $crab,
            'form' => $form->createView(),
            'type' => 'add',
        ];
    }

    #[Route(path: '/quality/derogations/{id}/edit', name: 'derogation_edit', defaults: ['label' => 'crab.title.edit_derogation', 'alvest_module' => 'DEROGATION', 'moduleDomain' => 'derogation'], methods: 'GET|POST')]
    #[Template('quality/derogation/write_derogation.html.twig')]
    #[IsGranted(attribute: 'FEATURE_DEROGATION_PARTIAL_UPDATE_VOTER', subject: new Expression('args["derogation"].getIri()'))]
    public function editDerogation(
        #[ApiValueResolverAttribute(parameters: ['resource' => self::DEROGATION_URL, 'id' => 'id'])] ApiData $derogation,
        Request $request)
    {
        $form = $this->createForm(DerogationType::class, $derogation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $payload = [
                '@id' => $derogation['@id'],
                'shortDescription' => $form->get('shortDescription')->getData(),
                'description' => $form->get('description')->getData(),
            ];

            try {
                $this->container->get(Client::class)->save(self::DEROGATION_URL, $payload);

                return $this->redirectToRoute('derogation_show', ['id' => Iri::id($derogation)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'derogation' => $derogation,
            'form' => $form->createView(),
            'type' => 'edit',
        ];
    }

    #[Route(path: '/quality/derogations/{id}/show', name: 'derogation_show', defaults: ['label' => 'crab.fields.derogation', 'alvest_module' => 'DEROGATION', 'moduleDomain' => 'derogation'], methods: 'GET|POST')]
    #[Template('quality/derogation/show_derogation.html.twig')]
    public function showDerogation(#[ApiValueResolverAttribute(parameters: ['resource' => self::DEROGATION_URL])] ApiData $derogation, Request $request)
    {
        if ('OPEN' === $derogation['status']) {
            $rescheduleForm = $this->createForm(DerogationRescheduleType::class, $derogation);
            $form = $this->createForm(DerogationEditType::class, ['assignee' => $derogation['assignee'], 'addFile' => true]);
            $decisionForm = $this->createForm(DecisionDerogationType::class);

            $form->handleRequest($request);
            $rescheduleForm->handleRequest($request);
            $decisionForm->handleRequest($request);
        } else {
            $rescheduleForm = null;
            $form = null;
            $decisionForm = null;
        }

        $client = $this->container->get(Client::class);
        if (null !== $form && $form->isSubmitted() && $form->isValid()) {
            try {
                $derogation = $client->save(\sprintf('%s/%s', self::DEROGATION_URL, Iri::id($derogation)), ['@id' => $derogation['@id'], ...$form->getData()]);
                $file = $form->get('file')->getData();
                if (null !== $file) {
                    $this->container->get(FileManager::class)->uploadFile(
                        $derogation,
                        $file,
                        self::DEROGATION_URL,
                        null,
                        'files',
                        false,
                        true
                    );
                }

                return $this->redirectToRoute('derogation_show', ['id' => Iri::id($derogation)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        if (null !== $rescheduleForm && $rescheduleForm->isSubmitted() && $rescheduleForm->isValid()) {
            $payload = [
                '@id' => $derogation['@id'],
                'dueDate' => $rescheduleForm->get('dueDate')->getData(),
            ];
            try {
                $client->save(\sprintf('%s/%s', self::DEROGATION_URL, Iri::id($derogation)), $payload);

                return $this->redirectToRoute('derogation_show', ['id' => Iri::id($derogation)]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $rescheduleForm);
            }
        }

        if (null !== $decisionForm && $decisionForm->isSubmitted() && $decisionForm->isValid()) {
            $status = null;
            if ($decisionForm->getClickedButton() && 'accepted' === $decisionForm->getClickedButton()->getName()) {
                $status = 'ACCEPTED';
            }
            if ($decisionForm->getClickedButton() && 'denied' === $decisionForm->getClickedButton()->getName()) {
                $status = 'DENIED';
            }

            $payload = array_merge(['status' => $status], $decisionForm->getData());
            try {
                $client->put(\sprintf('%s/%s/status', self::DEROGATION_URL, Iri::id($derogation)), ['json' => $payload]);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.derogation.status.success', [], 'crab'));
            } catch (ClientException $e) {
                $errorDescription = json_decode($e->getResponse()->getContent(false), true);
                $this->addFlash(
                    'error',
                    \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('crab.derogation.status.error', [], 'crab'), $errorDescription['hydra:description'])
                );
            }

            return $this->redirectToRoute('derogation_show', ['id' => Iri::id($derogation)]);
        }

        return [
            'derogation' => $derogation,
            'form' => $form?->createView(),
            'formReschedule' => $rescheduleForm?->createView(),
            'formDecision' => $decisionForm?->createView(),
        ];
    }

    #[Route(path: '/quality/derogations/{id}/delete', name: 'derogation_delete', methods: 'GET|POST')]
    #[IsGranted('FEATURE_DEROGATION_DELETE')]
    public function deleteDerogation(#[ApiValueResolverAttribute(parameters: ['resource' => self::DEROGATION_URL, 'id' => 'id'])] ApiData $derogation): RedirectResponse
    {
        try {
            $this->container->get(Client::class)->remove(self::DEROGATION_URL, Iri::id($derogation));
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.derogation.success.delete_derogation', [], 'crab'));

            return $this->redirectToRoute('derogation_home');
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash('error', \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('crab.derogation.errors.delete_derogation', [], 'crab'), $errorDescription['hydra:description']));

            return $this->redirectToRoute('derogation_show', ['id' => Iri::id($derogation)]);
        }
    }

    #[Route(path: '/quality/derogations/{id}/{status}', name: 'derogation_update_status', methods: 'GET|POST')]
    #[IsGranted('FEATURE_DEROGATION_STATUS_REOPEN')]
    public function updateDerogationStatus(#[ApiValueResolverAttribute(parameters: ['resource' => self::DEROGATION_URL, 'id' => 'id'])] ApiData $derogation, string $status)
    {
        try {
            $this->container->get(Client::class)->put(\sprintf('%s/%s/status', self::DEROGATION_URL, Iri::id($derogation)), ['json' => ['status' => $status]]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('crab.derogation.status.success', [], 'crab'));

            return $this->redirectToRoute('derogation_show', ['id' => Iri::id($derogation)]);
        } catch (ClientException $e) {
            $errorDescription = json_decode($e->getResponse()->getContent(false), true);
            $this->addFlash(
                'error',
                \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('crab.derogation.status.error', [], 'crab'), $errorDescription['hydra:description'])
            );
        }

        return $this->redirectToRoute('derogation_show', ['id' => Iri::id($derogation)]);
    }

    #[Route(path: '/quality/derogations/{derogationId}/files/{id}', name: 'derogation_file_show', requirements: ['id' => '\d+'], methods: 'GET')]
    public function showDerogationFile(#[ApiValueResolverAttribute(parameters: ['id' => 'derogationId', 'resource' => self::DEROGATION_URL])] ApiData $derogation,
        $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/files/%s', self::DEROGATION_URL, $derogation['id'], $id));
    }
}
