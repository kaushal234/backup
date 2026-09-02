<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\IndiceFactor;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallDefectivePart;
use App\Entity\Service\TechnicianOnCallType;
use App\Manager\Service\TechnicianOnCallTagFollowerPolicy;
use App\Request\SubRequestManager;
use App\Workflow\Handler\ChainWorkflowHandler;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Entity\WarrantyClaim;
use LegacyBundle\Manager\WarrantyClaimManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsEventListener(event: KernelEvents::VIEW, method: 'onReopen', priority: EventPriorities::PRE_VALIDATE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onEditionApplyInProgressStatus', priority: EventPriorities::PRE_VALIDATE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onTechnicianRequestedApplyPendingStatus', priority: EventPriorities::PRE_VALIDATE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'subscribeStandardFollowersForTag', priority: EventPriorities::POST_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'whenFactoryPaysCreateWC', priority: EventPriorities::POST_WRITE)]
#[AsEventListener(event: KernelEvents::RESPONSE, method: 'onIf1000OpenFactoryFlag', priority: EventPriorities::POST_WRITE)]
#[AsEventListener(event: KernelEvents::RESPONSE, method: 'onWriteWithHourMeterCreateTechnicianOnCallHourMeterTransaction')]
#[AsEventListener(event: KernelEvents::RESPONSE, method: 'customerServiceRecordCreation')]
class TechnicianOnCallListener extends AbstractTechnicianOnCallListener
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            IriConverterInterface::class,
            SubRequestManager::class,
            ValidatorInterface::class,
            EntityManagerInterface::class,
            ChainWorkflowHandler::class,
            WarrantyClaimManager::class,
            TranslatorInterface::class,
            TechnicianOnCallTagFollowerPolicy::class,
        ];
    }

    // Clear closure-specific information when a closed Technician On Call is reopened,
    // allowing a new diagnosis and resolution to be recorded.
    public function onReopen(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCall($event)) {
            return;
        }

        $request = $event->getRequest();
        /** @var TechnicianOnCall $previousData */
        $previousData = $request->attributes->get('previous_data');
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $event->getControllerResult();

        // Check if the status has changed from closed to opened (reopening)
        if (
            null === $previousData
            || !\in_array($previousData->status, TechnicianOnCall::CLOSED_STATUSES, true)
            || !\in_array($technicianOnCall->status, TechnicianOnCall::OPENED_STATUSES, true)
        ) {
            return;
        }

        $technicianOnCall->originalSymptoms = null;
        $technicianOnCall->symptoms = null;
        $technicianOnCall->originalRootCause = null;
        $technicianOnCall->rootCause = null;
        $technicianOnCall->originalSolution = null;
        $technicianOnCall->solution = null;

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        /** @var TechnicianOnCallDefectivePart $defectivePart */
        foreach ($technicianOnCall->getDefectiveParts() as $defectivePart) {
            // Do the remove here to keep the SoftDeleteable
            $entityManager->remove($defectivePart);
            // And remove from the collection
            $technicianOnCall->removeDefectivePart($defectivePart);
        }
    }

    public function onTechnicianRequestedApplyPendingStatus(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallRoute($event, 'technician_on_call_request_technician')) {
            return;
        }

        $technicianOnCall = $event->getControllerResult();
        $this->container->get(ChainWorkflowHandler::class)->handle($technicianOnCall, null);
    }

    public function onEditionApplyInProgressStatus(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallRoute($event, 'technician_on_call_edit')) {
            return;
        }

        $technicianOnCall = $event->getControllerResult();
        $this->container->get(ChainWorkflowHandler::class)->handle($technicianOnCall, null);
    }

    public function whenFactoryPaysCreateWC(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCall($event)) {
            return;
        }

        $technicianOnCall = $event->getControllerResult();
        $request = $event->getRequest();

        if (Request::METHOD_POST !== $request->getMethod() && Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        $previousData = $request->attributes->get('previous_data');
        if (
            TechnicianOnCallType::FACTORY !== $technicianOnCall->technicianOnCallType->name
            || (null !== $previousData && $previousData->technicianOnCallType->getId() === $technicianOnCall->technicianOnCallType->getId())
        ) {
            return;
        }

        /** @var WarrantyClaimManager $warrantyClaimManager */
        $warrantyClaimManager = $this->container->get(WarrantyClaimManager::class);
        $warranty = $warrantyClaimManager->findById($technicianOnCall->warrantyLegacyId);
        if ($warranty && WarrantyClaim::REJECTED === $warranty->status) {
            $warranty->status = WarrantyClaim::PENDING;

            $warrantyClaimManager->updateStatus($warranty);

            return;
        }

        if ($warranty) {
            return;
        }

        $warranty = $warrantyClaimManager->createFromTechnicianOnCall($technicianOnCall);
        $technicianOnCall->warrantyLegacyId = $warranty->getId();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        $entityManager->persist($technicianOnCall);
        $entityManager->flush();
    }

    public function subscribeStandardFollowersForTag(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCall($event)) {
            return;
        }
        $request = $event->getRequest();

        if (Request::METHOD_POST !== $request->getMethod() && Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $event->getControllerResult();

        /** @var TechnicianOnCallTagFollowerPolicy $technicianOnCallTagFollowerPolicy */
        $technicianOnCallTagFollowerPolicy = $this->container->get(TechnicianOnCallTagFollowerPolicy::class);
        $technicianOnCallTagFollowerPolicy->applyRule($technicianOnCall);
    }

    public function onWriteWithHourMeterCreateTechnicianOnCallHourMeterTransaction(ResponseEvent $event): void
    {
        if (!($technicianOnCall = $event->getRequest()->attributes->get('original_data')) instanceof TechnicianOnCall) {
            return;
        }

        if (
            null === $technicianOnCall->equipmentRecord
            || null === $technicianOnCall->hourMeter
            || (int) $technicianOnCall->hourMeter <= (int) $technicianOnCall->equipmentRecord->getHourMeter()
        ) {
            return;
        }

        $response = $this->postHourMeterTransaction($technicianOnCall);

        $mainResponse = $event->getResponse();
        if (300 <= $response->getStatusCode()) {
            $mainResponse->setStatusCode(Response::HTTP_PARTIAL_CONTENT);
        }

        $content = json_decode($mainResponse->getContent(), true);
        $content['@sub_resources']['hourMeter'] = json_decode($response->getContent(), true);

        $mainResponse->setContent(json_encode($content));
    }

    public function customerServiceRecordCreation(ResponseEvent $event): void
    {
        if (!($technicianOnCall = $event->getRequest()->attributes->get('original_data')) instanceof TechnicianOnCall) {
            return;
        }

        if (!$technicianOnCall->nestedCustomerServiceRecord) {
            return;
        }

        $response = $this->postNestedCustomerServiceRecord($technicianOnCall);

        $mainResponse = $event->getResponse();
        if (300 <= $response->getStatusCode()) {
            $mainResponse->setStatusCode(Response::HTTP_PARTIAL_CONTENT);
        }

        $content = json_decode($mainResponse->getContent(), true);
        $content['@sub_resources']['customerServiceRecord'] = json_decode($response->getContent(), true);

        $mainResponse->setContent(json_encode($content));
    }

    public function onIf1000OpenFactoryFlag(ResponseEvent $event): void
    {
        if (!($technicianOnCall = $event->getRequest()->attributes->get('original_data')) instanceof TechnicianOnCall) {
            return;
        }

        $operation = $event->getRequest()->attributes->get('_api_operation');

        if (!\in_array($operation->getName(), ['technician_on_call_create', 'technician_on_call_edit'], true)) {
            return;
        }

        if ($technicianOnCall->indiceFactor !== IndiceFactor::IF_1000->value) {
            return;
        }

        if ($operation instanceof Put) {
            $previousData = $event->getRequest()->attributes->get('previous_data');

            if ($previousData->indiceFactor === IndiceFactor::IF_1000->value) {
                return;
            }
        }

        $mainResponse = $event->getResponse();

        if (300 <= $mainResponse->getStatusCode()) {
            return;
        }

        $subRequestResponse = $this->postCommentToOpenFactoryFlag($technicianOnCall);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get(EntityManagerInterface::class);
        $entityManager->refresh($technicianOnCall);

        if (300 <= $subRequestResponse->getStatusCode()) {
            $mainResponse->setStatusCode(Response::HTTP_PARTIAL_CONTENT);
        }

        $content = json_decode($mainResponse->getContent(), true);
        $content['factoryFlag'] = $technicianOnCall->factoryFlag;
        $content['@sub_resources']['comment'] = json_decode($subRequestResponse->getContent(), true);

        $mainResponse->setContent(json_encode($content));
    }

    private function postCommentToOpenFactoryFlag(TechnicianOnCall $technicianOnCall): Response
    {
        /** @var SubRequestManager $subRequestManager */
        $subRequestManager = $this->container->get(SubRequestManager::class);

        /** @var IriConverterInterface $iriConverter */
        $iriConverter = $this->container->get(IriConverterInterface::class);

        /** @var TranslatorInterface $translator */
        $translator = $this->container->get(TranslatorInterface::class);

        return $subRequestManager->doSubRequest('api_comments_post_collection', [], Request::METHOD_POST, [
            'resource' => $iriConverter->getIriFromResource($technicianOnCall),
            'public' => false,
            'message' => $translator->trans('toc.comment.automatic_factory_flag', [], 'technician_on_call'),
            'metadata' => ['factoryFlag' => 'OPEN_FACTORY_FLAG'],
            'discriminator' => TechnicianOnCall::MODULE_NAME,
        ]);
    }

    private function postNestedCustomerServiceRecord(TechnicianOnCall $technicianOnCall): Response
    {
        /** @var SubRequestManager $subRequestManager */
        $subRequestManager = $this->container->get(SubRequestManager::class);

        /** @var IriConverterInterface $iriConverter */
        $iriConverter = $this->container->get(IriConverterInterface::class);

        return $subRequestManager->doSubRequest('technician_on_call_customer_service_records_post', [], Request::METHOD_POST, [
            'technicianOnCall' => $iriConverter->getIriFromResource($technicianOnCall),
            'equipmentRecord' => $iriConverter->getIriFromResource($technicianOnCall->equipmentRecord),
            'title' => $technicianOnCall->title,
            'description' => $technicianOnCall->description,
            'airport' => $iriConverter->getIriFromResource($technicianOnCall->airport),
            'leader' => $technicianOnCall->nestedCustomerServiceRecord->leader ? $iriConverter->getIriFromResource($technicianOnCall->nestedCustomerServiceRecord->leader) : null,
            'plannedAt' => $technicianOnCall->nestedCustomerServiceRecord->plannedAt ? $technicianOnCall->nestedCustomerServiceRecord->plannedAt->format('Y-m-d H:i:s') : null,
        ]);
    }

    private function postHourMeterTransaction(TechnicianOnCall $technicianOnCall): Response
    {
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = $this->container->get(IriConverterInterface::class);

        /** @var SubRequestManager $subRequestManager */
        $subRequestManager = $this->container->get(SubRequestManager::class);

        return $subRequestManager->doSubRequest('toc_hour_meter_transaction_post', [], Request::METHOD_POST, [
            'technicianOnCall' => $iriConverter->getIriFromResource($technicianOnCall),
            'hourMeter' => $technicianOnCall->hourMeter,
        ]);
    }
}
