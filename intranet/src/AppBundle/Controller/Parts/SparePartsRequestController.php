<?php

declare(strict_types=1);

namespace AppBundle\Controller\Parts;

use ApiBundle\Client;
use ApiBundle\Form\ViolationMapper;
use ApiBundle\Http\FileStreamedResponseFactory;
use ApiBundle\Iri\Iri;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use AppBundle\Controller\Sales\CustomerRelationshipTeamController;
use AppBundle\Controller\Sales\ExtranetUserController;
use AppBundle\Controller\Support\EquipmentSerialsController;
use AppBundle\DataPersister\Service\TechnicianOnCallPersister;
use AppBundle\DataTable\Query\ApiProxyQuery;
use AppBundle\DataTable\Type\Parts\SparePartsRequest\DeliveryAddressesDataTableType;
use AppBundle\DataTable\Type\Parts\SparePartsRequest\SparePartsRequestDataTableType;
use AppBundle\Form\Type\IdSearchType;
use AppBundle\Form\Type\Parts\PartsCollectionType;
use AppBundle\Form\Type\Parts\SparePartsRequestCombineType;
use AppBundle\Form\Type\Parts\SparePartsRequestStatusType;
use AppBundle\Form\Type\Parts\SparePartsRequestType;
use AppBundle\Manager\FileManager;
use AppBundle\Manager\Service\TechnicianOnCallManager;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/parts/spare-parts-requests', defaults: ['alvest_module' => 'SPR', 'moduleDomain' => 'spare_parts_request'])]
class SparePartsRequestController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;
    public const string DELETE_TOKEN_FILE = 'delete_spare_parts_request_file';
    final public const SPARE_PARTS_REQUESTS_URL = 'parts/spare_parts_requests';
    final public const SPARE_PARTS_REQUESTS_PART_URL = 'parts/spare_parts_request_parts';
    final public const TOC_SPARE_PARTS_REQUESTS_URL = 'parts/toc_spare_parts_requests';
    final public const SB_SPARE_PARTS_REQUESTS_URL = 'parts/sb_spare_parts_requests';
    final public const SPARE_PARTS_REQUESTS_DELIVERY_ADDRESSES_URL = 'parts/spare_parts_request_delivery_addresses';

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            Client::class,
            ViolationMapper::class,
            TranslatorInterface::class,
            FileManager::class,
            FileStreamedResponseFactory::class,
            FormFactoryInterface::class,
            TechnicianOnCallManager::class,
        ]);
    }

    #[Route(path: '', name: 'spare_parts_request_home', methods: ['GET'])]
    #[Template('parts/spare_parts_requests/home.html.twig')]
    public function home()
    {
        $client = $this->container->get(Client::class);

        return [
            'bySphWarranty' => $client->get('reports/resource=/parts/spare_parts_requests;x=sph.name;y=status', ['query' => ['options' => ['warranty' => true]]]),
            'bySph' => $client->get('reports/resource=/parts/spare_parts_requests;x=sph.name;y=status', ['query' => ['options' => ['warranty' => false]]]),
            'byShippingOriginWarranty' => $client->get('reports/resource=/parts/spare_parts_requests;x=parts.shippingOrigin.name;y=status', ['query' => ['options' => ['warranty' => true]]]),
            'byShippingOrigin' => $client->get('reports/resource=/parts/spare_parts_requests;x=parts.shippingOrigin.name;y=status', ['query' => ['options' => ['warranty' => false]]]),
        ];
    }

    #[Route(path: '/search', name: 'spare_parts_request_list', methods: ['GET', 'POST'], defaults: ['label' => 'menu.search', 'domain' => 'messages'])]
    #[Template('parts/spare_parts_requests/list.html.twig')]
    public function list(Request $request)
    {
        $dataTable = $this->createDataTable(SparePartsRequestDataTableType::class, self::SPARE_PARTS_REQUESTS_URL);
        $dataTable->handleRequest($request);

        if ($dataTable->isExporting() && $dataTable->getQuery() instanceof ApiProxyQuery) {
            return $dataTable->getQuery()->export();
        }

        if ($dataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($dataTable);
        }

        $client = $this->container->get(Client::class);
        $idSearchForm = $this->createForm(IdSearchType::class, null, [
            'id_label' => false,
            'id_placeholder' => 'By ID',
        ]);

        $idSearchForm->handleRequest($request);
        if ($idSearchForm->isSubmitted() && $idSearchForm->isValid()) {
            $id = $idSearchForm->get('id')->getData();
            try {
                $client->get(\sprintf('%s/%s', self::SPARE_PARTS_REQUESTS_URL, $id));

                return $this->redirectToRoute('spare_parts_request_show', ['id' => $id]);
            } catch (ClientException $e) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.errors.not_exist', ['%id%' => $id], 'spare_parts_request'));
            }
        }

        return [
            'dataTable' => $dataTable->createView(),
            'idSearchForm' => $idSearchForm->createView(),
        ];
    }

    #[Route(path: '/{id}/show', name: 'spare_parts_request_show', methods: ['GET', 'POST'])]
    #[Template('parts/spare_parts_requests/show.html.twig')]
    public function show(#[ApiValueResolverAttribute(parameters: ['resource' => self::SPARE_PARTS_REQUESTS_URL, 'allowedTypes' => ['tocSparePartsRequest', 'sbSparePartsRequest']])] ApiData $sparePartsRequest, Request $request)
    {
        $existingParts = [];
        foreach ($sparePartsRequest['parts'] as $part) {
            $existingParts[$part['id']] = $part;
        }

        $client = $this->container->get(Client::class);
        $translator = $this->container->get(TranslatorInterface::class);
        $form = $this->createForm(PartsCollectionType::class, ['parts' => $existingParts] + $sparePartsRequest->toArray());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitButton = $form->get('submit');
            if ($submitButton instanceof SubmitButton && $submitButton->isClicked()) {
                $partsToSave = [];
                foreach ($form->get('parts')->getData() as $submittedPart) {
                    if ($existingParts[$submittedPart['id']]['partNumber'] !== $submittedPart['partNumber']) {
                        $partsToSave[] = [
                            'partNumber' => $submittedPart['partNumber'],
                            'description' => $submittedPart['description'],
                            'unitOfMeasure' => $submittedPart['unitOfMeasure'],
                            'comment' => $submittedPart['comment'],
                            'quantity' => (int) $submittedPart['quantity'],
                        ];

                        continue;
                    }

                    $partsToSave[] = $submittedPart;
                }
                try {
                    $client->save(self::SPARE_PARTS_REQUESTS_URL, ['@id' => $sparePartsRequest->getIri(), 'parts' => $partsToSave]);
                    $this->addFlash('success', $translator->trans('spare_parts_request.success.edition', [], 'spare_parts_request'));

                    return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id'], 'tab' => 'parts']);
                } catch (ClientException $e) {
                    $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
                    $request->query->set('tab', 'parts');
                }
            }

            /** @var SubmitButton $moveToAnotherSparePartsRequestButton */
            $moveToAnotherSparePartsRequestButton = $form->get('moveToAnotherSparePartsRequest');
            /** @var SubmitButton $createNewSparePartRequestButton */
            $createNewSparePartRequestButton = $form->get('createNewSparePartRequest');
            if ($moveToAnotherSparePartsRequestButton->isClicked() || $createNewSparePartRequestButton->isClicked()) {
                $newParts = [];
                $existingPartsToSave = [];
                foreach ($form->get('parts')->getData() as $partData) {
                    if (false === $partData['move']) {
                        $existingPartsToSave[] = $partData['@id'];
                    }
                    foreach ($existingParts as $existingPart) {
                        if ($existingPart['@id'] === $partData['@id'] && true === $partData['move']) {
                            if ($existingPart['quantity'] === (int) $partData['quantity']) {
                                $newParts[] = $partData['@id'];
                                continue;
                            }

                            $newParts[] = [
                                'partNumber' => $partData['partNumber'],
                                'description' => $partData['description'],
                                'unitOfMeasure' => $partData['unitOfMeasure'],
                                'comment' => $partData['comment'],
                                'quantity' => (int) $partData['quantity'],
                            ];

                            $existingPartsToSave[] = [
                                '@id' => $existingPart['@id'],
                                'quantity' => $existingPart['quantity'] - (int) $partData['quantity'],
                            ];
                        }
                    }
                }

                if (($targetSparePartsRequestIri = $form->get('sparePartsRequest')->getData()) !== null && $moveToAnotherSparePartsRequestButton->isClicked()) {
                    $targetSparePartsRequest = $client->get($targetSparePartsRequestIri);
                    $newParts = array_merge($newParts, $targetSparePartsRequest['parts']);
                    try {
                        $client->save(self::SPARE_PARTS_REQUESTS_URL, ['@id' => $targetSparePartsRequestIri, 'parts' => $newParts]);
                        $this->addFlash('success', $translator->trans('spare_parts_request.success.parts_move', [], 'spare_parts_request'));

                        if ([] !== $existingPartsToSave) {
                            $client->save(self::SPARE_PARTS_REQUESTS_URL, ['@id' => $sparePartsRequest->getIri(), 'parts' => $existingPartsToSave]);
                        }

                        return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id'], 'tab' => 'parts']);
                    } catch (ClientException $e) {
                        $this->addFlash('error', $translator->trans('spare_parts_request.errors.parts_move', [], 'spare_parts_request'));

                        return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id'], 'tab' => 'parts']);
                    }
                }
                if ($createNewSparePartRequestButton->isClicked()) {
                    $iriType = $sparePartsRequest->getIriType();
                    $authorizedFields = $client->get('/fields', ['query' => ['iri' => $iriType, 'method' => Request::METHOD_POST]]);
                    $newSparePartsRequest = [];
                    foreach ($authorizedFields as $authorizedField) {
                        $value = $sparePartsRequest[$authorizedField];
                        $newSparePartsRequest[$authorizedField] = $value['@id'] ?? $value;
                    }
                    $newSparePartsRequest['equipmentRecords'] = array_column($newSparePartsRequest['equipmentRecords'], '@id');
                    $newSparePartsRequest['parts'] = $newParts;
                    unset($newSparePartsRequest['salesOrder']);
                    try {
                        $response = $client->save($iriType, $newSparePartsRequest);
                        $this->addFlash('success', $translator->trans('spare_parts_request.success.parts_move_new', [], 'spare_parts_request'));

                        if ([] !== $existingPartsToSave) {
                            $client->save(self::SPARE_PARTS_REQUESTS_URL, ['@id' => $sparePartsRequest->getIri(), 'parts' => $existingPartsToSave]);
                        }

                        return $this->redirectToRoute('spare_parts_request_show', ['id' => $response['id']]);
                    } catch (ClientException $e) {
                        $this->addFlash('error', $translator->trans('spare_parts_request.errors.parts_move', [], 'spare_parts_request'));

                        return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id'], 'tab' => 'parts']);
                    }
                }
            }
        }

        $combineForm = $this->createForm(SparePartsRequestCombineType::class, $sparePartsRequest->toArray());
        $combineForm->handleRequest($request);
        if ($combineForm->isSubmitted() && $combineForm->isValid()) {
            try {
                $this->container->get(Client::class)->put(\sprintf('%s/%s/combine', self::SPARE_PARTS_REQUESTS_URL, $sparePartsRequest->getIriId()), ['json' => [
                    'sparePartsRequests' => $combineForm->get('sparePartsRequests')->getData(),
                ]]);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.success.combine', [], 'spare_parts_request'));

                return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $combineForm);
            }
        }

        $statusForm = null;
        $nextStatuses = ['PENDING' => 'OPEN', 'OPEN' => 'SHIPPED', 'SHIPPED' => 'CLOSED'];
        if ($nextStatus = $nextStatuses[$sparePartsRequest['status']] ?? null) {
            $statusForm = $this->createForm(SparePartsRequestStatusType::class, $sparePartsRequest);
            $statusForm->handleRequest($request);
            if ($statusForm->isSubmitted() && $statusForm->isValid()) {
                try {
                    $comment = $statusForm->get('comment')->getData();
                    if ($statusForm->has('proofOfDelivery') && ($proofOfDelivery = $statusForm->get('proofOfDelivery')->getData()) instanceof UploadedFile) {
                        $this->container->get(FileManager::class)->uploadFile($sparePartsRequest, $proofOfDelivery, self::SPARE_PARTS_REQUESTS_URL, null, 'proof_of_delivery');

                        return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id']]);
                    }
                    if ('' === (string) $comment && $statusForm->has('proofOfDelivery') && null === $statusForm->get('proofOfDelivery')->getData()) {
                        $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.status.manually_leave_SHIPPED_explanation', [], 'spare_parts_request'));

                        return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id']]);
                    }
                    if (null !== $comment) {
                        $client->save('comments', [
                            'resource' => $sparePartsRequest->getIri(),
                            'message' => $translator->trans('spare_parts_request.status.manually_leave_comment', ['%status%' => $nextStatus, '%comment%' => $statusForm->get('comment')->getData()], 'spare_parts_request'),
                        ]);
                    }
                    $client->put(\sprintf('%s/%d/status', self::SPARE_PARTS_REQUESTS_URL, $sparePartsRequest->getIriId()), ['json' => ['status' => $nextStatus]]);
                    $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.success.send_to', ['%status%' => $nextStatus], 'spare_parts_request'));

                    return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id']]);
                } catch (ClientException $e) {
                    $errorDescription = json_decode($e->getResponse()->getContent(false), true);
                    $this->addFlash('error', \sprintf('%s. %s', $this->container->get(TranslatorInterface::class)->trans('security.warning.access_denied', [], 'messages'), $errorDescription['hydra:description']));
                }
            }
        }

        return [
            'sparePartsRequest' => $sparePartsRequest,
            'tab' => $request->query->get('tab'),
            'form' => $form->createView(),
            'combineForm' => $combineForm->createView(),
            'statusForm' => $statusForm instanceof FormInterface ? $statusForm->createView() : null,
        ];
    }

    #[Route(path: '/{id}/parts/{partId}/delete', name: 'spare_parts_request_part_delete', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_PARTS') or is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR')"))]
    public function deletePart(#[ApiValueResolverAttribute(parameters: ['resource' => self::SPARE_PARTS_REQUESTS_URL, 'allowedTypes' => ['tocSparePartsRequest', 'sbSparePartsRequest']])] ApiData $sparePartsRequest, int $partId, Request $request)
    {
        $referer = $request->headers->get('referer');
        $parts = [];
        foreach ($sparePartsRequest['parts'] as $part) {
            if ($part['id'] !== $partId) {
                $parts[] = $part['@id'];
            }
        }

        try {
            $this->container->get(Client::class)->save(self::SPARE_PARTS_REQUESTS_URL, ['@id' => $sparePartsRequest->getIri(), 'parts' => $parts]);
            $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.success.remove_part', [], 'spare_parts_request'));
        } catch (ClientException $exception) {
            $this->addFlash(
                'error',
                $exception->getMessage()
            );

            return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest->getIriId()]);
        }

        return null !== $referer ? $this->redirect($referer) : $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id'], 'tab' => 'parts']);
    }

    #[Route(path: '/{id}/parts/{partId}/restore', name: 'spare_parts_request_part_restore', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_PARTS') or is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR')"))]
    public function restorePart(#[ApiValueResolverAttribute(parameters: ['resource' => self::SPARE_PARTS_REQUESTS_URL, 'allowedTypes' => ['tocSparePartsRequest', 'sbSparePartsRequest']])] ApiData $sparePartsRequest, int $partId): RedirectResponse
    {
        $parts = $sparePartsRequest['parts'];
        foreach ($sparePartsRequest['deletedParts'] as $deletedPart) {
            if ($deletedPart['id'] === $partId && null !== $deletedPart['deletedAt']) {
                $this->container->get(Client::class)->remove(self::SPARE_PARTS_REQUESTS_PART_URL, $deletedPart['id']);
                unset($deletedPart['@id']);
                $newPart = [$deletedPart];
                foreach ($parts as $index => $part) {
                    if ($deletedPart['partNumber'] === $part['partNumber']) {
                        $parts[$index]['quantity'] += $deletedPart['quantity'];
                        $newPart = [];
                    }
                }
                $this->container->get(Client::class)->save(self::SPARE_PARTS_REQUESTS_URL, [
                    '@id' => $sparePartsRequest->getIri(),
                    'parts' => array_merge($parts, $newPart),
                ]);
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.success.restore_part', [], 'spare_parts_request'));
            }
        }

        return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id'], 'tab' => 'parts']);
    }

    #[Route(path: '/{id}/edit', name: 'spare_parts_request_edit', methods: ['GET', 'POST'])]
    #[Template('parts/spare_parts_requests/edit.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR')"))]
    public function edit(#[ApiValueResolverAttribute(parameters: ['resource' => self::SPARE_PARTS_REQUESTS_URL, 'allowedTypes' => ['tocSparePartsRequest', 'sbSparePartsRequest']])] ApiData $sparePartsRequest, Request $request)
    {
        $form = $this->createForm(SparePartsRequestType::class, $sparePartsRequest->toArray());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $this->container->get(Client::class);
            $data = $form->getData();
            $keys = ['@id'];
            foreach ($form->all() as $child) {
                if ($child instanceof SubmitButton || true === $child->getConfig()->getOption('disabled')) {
                    continue;
                }
                $keys[] = $child->getName();
            }
            try {
                $client->save(self::SPARE_PARTS_REQUESTS_URL, array_intersect_key($data, array_flip($keys)));
                $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.success.edition', [], 'spare_parts_request'));

                return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequest['id']]);
            } catch (ClientException $e) {
                $this->container->get(ViolationMapper::class)->mapToForm($e, $form);
            }
        }

        return [
            'form' => $form->createView(),
            'sparePartsRequest' => $sparePartsRequest,
        ];
    }

    #[Route(path: '/{sparePartsRequestId}/proof_of_delivery/{id}', name: 'spare_parts_request_proof_of_delivery', methods: 'GET')]
    public function downloadProofOfDelivery(int $sparePartsRequestId, int $id)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/proof_of_delivery/%s', self::SPARE_PARTS_REQUESTS_URL, $sparePartsRequestId, $id));
    }

    #[Route(path: '/toc/{id}/add-parts', name: 'spare_parts_request_toc_add_parts', requirements: ['id' => '\d+'], methods: ['GET', 'POST'], defaults: ['label' => 'spare_parts_request.title.add_from_toc'])]
    #[Template('parts/spare_parts_requests/toc.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_SPARE_PARTS_REQUESTS_CREATE') or is_granted('MOO_SPR')"))]
    public function tocAddParts(
        Request $request,
        #[ApiValueResolverAttribute(parameters: ['resource' => TechnicianOnCallPersister::RESOURCE_URL])] ApiData $technicianOnCall,
    ) {
        $client = $this->container->get(Client::class);
        $tocRedirection = $this->redirectToRoute('technician_on_calls_show', ['id' => $technicianOnCall['id']]);

        foreach (['activity', 'type'] as $requiredParameter) {
            if (!$request->query->has($requiredParameter)) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.errors.toc_no_'.$requiredParameter, [], 'spare_parts_request'));

                return $tocRedirection;
            }
        }

        try {
            $newSparePartsRequest = $client->get(\sprintf('/parts/spare_parts_request_from_toc/%s', $technicianOnCall['id']), ['headers' => ['Accept' => 'application/json']]);
            $existingSparePartsRequest = $newSparePartsRequest['sparePartsRequest'];
            unset($newSparePartsRequest['sparePartsRequest']);
        } catch (ClientException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $tocRedirection;
        }

        $newSparePartsRequest = [
            ...$newSparePartsRequest,
            'activity' => $request->query->get('activity'),
            'type' => $request->query->get('type'),
            'technicianOnCall' => $technicianOnCall['@id'],
            'tocId' => $technicianOnCall['id'],
        ];

        $extranetUsers = $client->findBy(ExtranetUserController::RESOURCE_URL, ['extranetUserProfile.customer' => $newSparePartsRequest['customers'], 'extranetUserProfile.archived' => false], [], ['raw_results' => true]);

        return [
            'tocId' => $technicianOnCall['id'],
            'initialState' => [
                'location' => [
                    'sparePartsHubs' => $client->findBy('locations', ['capability.sparePartsHub' => 1], ['name' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
                    'locations' => $client->findBy('locations', ['erpInLN' => 1], ['name' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
                ],
                'extranetUser' => [
                    'extranetUsers' => $extranetUsers['hydra:member'],
                ],
                'part' => ['technicianOnCallParts' => $this->container->get(TechnicianOnCallManager::class)->getItemMonologisticPartsFromList($technicianOnCall, $request->query->all('parts'))],
            ],
            'props' => [
                'customers' => $newSparePartsRequest['customers'],
                'airport' => $newSparePartsRequest['airport'] ?? null,
                'sparePartsRequest' => $existingSparePartsRequest,
                'newSparePartsRequest' => $newSparePartsRequest,
                'location' => $newSparePartsRequest['sso'],
            ],
        ];
    }

    #[Route(path: '/sb/{sbId}/create', name: 'spare_parts_request_sb_create', requirements: ['sbId' => '\d+'], methods: ['GET'], defaults: ['label' => 'spare_parts_request.title.add_from_sb'])]
    #[Template('parts/spare_parts_requests/sb.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_SPARE_PARTS_REQUESTS_CREATE') or is_granted('MOO_SPR')"))]
    public function createFromSB(Request $request, int $sbId)
    {
        foreach (['factory', 'category', 'equipmentRecords', 'decision'] as $requiredQueryParameter) {
            if (!$request->query->has($requiredQueryParameter)) {
                $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.errors.sb_no_'.$requiredQueryParameter, [], 'spare_parts_request'));

                return $this->redirectToRoute('legacy_product_support', ['m' => ['sb', 'view', 'summary', 'implementation'], 'id' => $sbId]);
            }
        }

        $equipmentRecordsIris = $request->query->all()['equipmentRecords'] ?? [];
        $equipmentRecordsIds = [];
        foreach ($equipmentRecordsIris as $iri => $decision) {
            $equipmentRecordsIds[] = Iri::id($iri);
        }

        $equipmentRecords = $this->container->get(Client::class)->findBy(EquipmentSerialsController::EQUIPMENT_RECORD_URL, ['id' => $equipmentRecordsIds, 'normalization_groups' => ['iata_code_detail']], [], ['raw_results' => true]);

        $type = null;
        switch ($request->query->get('category')) {
            case 'INFORMATION':
                $type = 'SSO';
                break;
            case 'COMPULSORY':
            case 'RECOMMENDED':
                $type = 'Factory';
        }

        if ('C' === $request->query->get('decision')) {
            $type = 'Payable Services';
        }

        $sparePartsRequests = [];
        $extranetUsers = [];
        $factory = null;
        $sso = null;
        $sph = null;
        foreach ($equipmentRecords['hydra:member'] as $equipmentRecord) {
            foreach (['salesOrganisation' => 'er_no_sales_organisation', 'manufacturerLocation' => 'er_no_manufacturer_location', 'endUser' => 'er_no_end_user', 'airport' => 'er_no_airport'] as $requiredQueryParameter => $translationKey) {
                if (null === $equipmentRecord[$requiredQueryParameter]) {
                    $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.errors.'.$translationKey, [], 'spare_parts_request'));

                    return $this->redirectToRoute('legacy_product_support', ['m' => ['sb', 'view', 'summary', 'implementation'], 'id' => $sbId]);
                }
            }

            $key = \sprintf('%s-%s-%s', $equipmentRecord['endUser']['@id'], $equipmentRecord['airport']['@id'], $equipmentRecordsIris[$equipmentRecord['@id']]);
            if (!isset($sparePartsRequests[$key])) {
                if (null === $sph) {
                    $crts = $this->container->get(Client::class)->findBy(CustomerRelationshipTeamController::RESOURCE_URL, ['customer' => $equipmentRecord['endUser']['@id'], 'erpLocation' => $equipmentRecord['salesOrganisation']['@id']], ['id' => 'DESC']);
                    foreach ($crts as $crt) {
                        if (null !== $crt['partsLocation']) {
                            $sph = $crt['partsLocation'];
                        }
                    }
                }

                if (null === $factory) {
                    $factory = $equipmentRecord['manufacturerLocation'];
                }

                if (null === $sso) {
                    $sso = $equipmentRecord['salesOrganisation'];
                }

                $sparePartsRequests[$key] = [
                    'customer' => $equipmentRecord['endUser'],
                    'airport' => $equipmentRecord['airport'] ?? null,
                    'factory' => $factory,
                    'erpLocation' => $factory,
                    'sph' => $sph,
                    'sso' => $sso,
                    'sbId' => $sbId,
                    'activity' => 'Service Bulletin',
                    'type' => $type,
                ];

                $customers = [];
                foreach ([$equipmentRecord['buyer'], $equipmentRecord['endUser'], $equipmentRecord['maintainer'] ?? []] as $customer) {
                    if (isset($customer['@id']) && !\in_array($customer['@id'], $customers, true)) {
                        $customers[] = $customer['@id'];
                    }
                }

                $linkedContacts = $this->container->get(Client::class)->findBy(ExtranetUserController::RESOURCE_URL, ['extranetUserProfile.customer' => $customers], [], ['raw_results' => true]);
                $extranetUsers[\sprintf('%s-%s', $equipmentRecord['airport']['@id'], $equipmentRecord['endUser']['@id'])] = $linkedContacts['hydra:member'];
            }
            $sparePartsRequests[$key]['equipmentRecords'][] = $equipmentRecord;
        }

        $parts = [];
        foreach (($request->query->all()['parts'] ?? []) as $partNumber => $quantity) {
            // not sure it's worth fetching the description of the part from the API or not, usually it's just one part called SB1234,
            // but that might be better to get that from Baan...
            $parts[] = ['partNumber' => (string) $partNumber, 'quantity' => $quantity];
        }

        if ([] === $parts) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('spare_parts_request.errors.no_parts', [], 'spare_parts_request'));

            return $this->redirectToRoute('legacy_product_support', ['m' => ['sb', 'view', 'summary', 'implementation'], 'id' => $sbId]);
        }

        foreach ($sparePartsRequests as &$sparePartsRequest) {
            foreach ($parts as $part) {
                $quantity = $part['quantity'] * \count($sparePartsRequest['equipmentRecords']);

                $sparePartsRequest['parts'][] = [
                    'partNumber' => $part['partNumber'],
                    'quantity' => $quantity,
                ];
            }
        }

        return [
            'sbId' => $sbId,
            'factory' => $request->query->get('factory'),
            'equipmentRecords' => $equipmentRecords,
            'initialState' => [
                'spr' => [
                    'sparePartsRequests' => array_values($sparePartsRequests),
                ],
                'extranetUser' => [
                    'extranetUsers' => $extranetUsers,
                ],
                'location' => [
                    'sparePartsHubs' => $this->container->get(Client::class)->findBy('locations', ['capability.sparePartsHub' => 1], ['name' => 'asc'], ['raw_results' => true])['hydra:member'] ?? [],
                ],
            ],
        ];
    }

    #[Route(path: '/{id}/edit-address', name: 'spare_parts_request_edit_address', methods: ['GET|POST'], defaults: ['label' => 'spare_parts_request.edit_address'])]
    #[Template('parts/spare_parts_requests/edit_address.html.twig')]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL') or is_granted('MOO_SPR')"))]
    public function editAddress(#[ApiValueResolverAttribute(parameters: ['resource' => self::SPARE_PARTS_REQUESTS_URL, 'allowedTypes' => ['tocSparePartsRequest', 'sbSparePartsRequest']])] ApiData $sparePartsRequest)
    {
        $extranetUsers = $this->container->get(Client::class)->findBy(ExtranetUserController::RESOURCE_URL, ['extranetUserProfile.customer' => $sparePartsRequest['customer']['@id']], [], ['raw_results' => true]);

        return [
            'initialState' => [
                'extranetUser' => [
                    'extranetUsers' => $extranetUsers['hydra:member'],
                ],
            ],
            'props' => [
                'sparePartsRequest' => $sparePartsRequest->toArray(),
            ],
            'sparePartsRequest' => $sparePartsRequest,
        ];
    }

    #[Route(path: '/{id}/show_file_ajax', name: 'spare_parts_request_show_file_ajax', methods: ['GET'])]
    #[Template('parts/spare_parts_requests/partial/files_ajax.html.twig')]
    public function ajax_list_files(#[ApiValueResolverAttribute] ApiData $sparePartsRequest,
    ): array {
        return compact('sparePartsRequest');
    }

    #[Route(path: '/{sparePartsRequestId}/file/{fileId}', name: 'spare_parts_request_download_file', methods: ['GET'])]
    public function download_file(int $sparePartsRequestId, int $fileId)
    {
        return $this->container->get(FileStreamedResponseFactory::class)->create(\sprintf('%s/%s/spare_parts_request/%s', self::SPARE_PARTS_REQUESTS_URL, $sparePartsRequestId, $fileId));
    }

    #[Route(path: '/{sparePartsRequestId}/files/{fileId}/delete', name: 'spare_parts_request_delete_file', methods: ['GET'])]
    public function delete_file(int $sparePartsRequestId, int $fileId, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid(self::DELETE_TOKEN_FILE, $request->query->get('_token'))) {
            $this->addFlash('error', $this->container->get(TranslatorInterface::class)->trans('delete.errors', [], 'file_type'));

            return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequestId]);
        }
        $this->container->get(Client::class)->remove(\sprintf('parts/spare_parts_requests/%d/files', $sparePartsRequestId), $fileId);
        $this->addFlash('success', $this->container->get(TranslatorInterface::class)->trans('task.message.delete_file', [], 'task'));

        return $this->redirectToRoute('spare_parts_request_show', ['id' => $sparePartsRequestId]);
    }

    #[Route(path: '/delivery_addresses', name: 'delivery_addresses', methods: ['GET'], defaults: ['label' => 'contacts.fields.delivery_addresses', 'domain' => 'contacts'])]
    #[IsGranted(attribute: new Expression("is_granted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL')  or is_granted('FEATURE_SPARE_PARTS_REQUESTS_SHIPPED_TO_CLOSE') or is_granted('MOO_SPR')"))]
    #[Template('parts/spare_parts_requests/delivery_addresses.html.twig')]
    public function deliveryAddresses(Request $request)
    {
        $deliveryAddressesDataTable = $this->createDataTable(DeliveryAddressesDataTableType::class, self::SPARE_PARTS_REQUESTS_DELIVERY_ADDRESSES_URL);
        $deliveryAddressesDataTable->handleRequest($request);

        if ($deliveryAddressesDataTable->isRequestFromTurboFrame()) {
            return $this->createDataTableTurboResponse($deliveryAddressesDataTable);
        }

        return [
            'deliveryAddressesDataTable' => $deliveryAddressesDataTable->createView(),
        ];
    }

    #[Route(path: '/delivery_addresses/{id}/toggle-archive', name: 'delivery_address_toggle_archive', methods: ['GET'])]
    public function toggleArchive(int $id, Request $request): RedirectResponse
    {
        $this->container->get(Client::class)->request(
            self::SPARE_PARTS_REQUESTS_DELIVERY_ADDRESSES_URL,
            $id,
            'toggle_archive',
            Request::METHOD_PATCH,
        );

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('delivery_addresses'));
    }
}
