<?php

declare(strict_types=1);

namespace App\EventListener\Parts;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Client\Exception\SoapException;
use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\SparePartsRequestPart;
use App\Entity\Parts\TOCSparePartsRequest;
use App\Entity\Service\TechnicianOnCallType;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Filter\Warehousing\Shipments\ShipmentOrderFilter;
use App\ION\Manager\MasterData\EnterpriseModel\EnterpriseStructure\SiteToLocationConverter;
use App\ION\Resources\Sales\SalesOrder;
use App\ION\Resources\Warehousing\Shipments\Shipment;
use App\Notifier\Parts\SparePartsRequestNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\SparePartsRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SparePartsRequestListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    private array $previousParts = [];

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onSparePartsRequestCreation', EventPriorities::POST_WRITE],
                ['beforeSparePartsRequestEdition', EventPriorities::PRE_WRITE],
                ['afterSparePartsRequestEdition', EventPriorities::POST_WRITE],
                ['onDeliveryAddressUpdate', EventPriorities::POST_WRITE],
            ],
            KernelEvents::REQUEST => [
                ['afterSparePartsRequestItemRead', EventPriorities::POST_READ],
                ['afterSparePartsRequestCollectionRead', EventPriorities::POST_READ],
                ['afterSparePartsRequestDeserialize', EventPriorities::POST_DESERIALIZE],
            ],
        ];
    }

    public function onSparePartsRequestCreation(ViewEvent $event)
    {
        $sparePartsRequest = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$sparePartsRequest instanceof SparePartsRequest || !$request->attributes->get('_api_operation') instanceof Post) {
            return;
        }

        $this->serviceLocator->get(SparePartsRequestNotifier::class)->notifyCreation($sparePartsRequest);
    }

    public function beforeSparePartsRequestEdition(ViewEvent $event): void
    {
        $sparePartsRequest = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$sparePartsRequest instanceof SparePartsRequest || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $id = $sparePartsRequest->getId();
        $this->previousParts[$id] = [];

        foreach ($this->serviceLocator->get(EntityManagerInterface::class)->getRepository(SparePartsRequestPart::class)->getPartsIds($sparePartsRequest) as $previousPart) {
            $this->previousParts[$id][$previousPart['partNumber']] = null !== $previousPart['deletedAt'];
        }

        $status = $sparePartsRequest->getStatus();

        if (
            null !== $sparePartsRequest->salesOrder
            && SparePartsRequest::STATUS_PENDING === $status
        ) {
            $sparePartsRequest->setStatus(SparePartsRequest::STATUS_OPEN);
        }
    }

    public function afterSparePartsRequestEdition(ViewEvent $event): void
    {
        $sparePartsRequest = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$sparePartsRequest instanceof SparePartsRequest || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $this->populateSparePartsRequestFromION($sparePartsRequest);

        $status = $sparePartsRequest->getStatus();

        /** @var SparePartsRequestNotifier $notifier */
        $notifier = $this->serviceLocator->get(SparePartsRequestNotifier::class);

        /** @var SparePartsRequest $previousData */
        $previousData = $request->attributes->get('previous_data');

        if (SparePartsRequest::STATUS_SHIPPED === $status && $previousData->getStatus() !== $status) {
            $notifier->notifyShipping($sparePartsRequest);
        }

        $id = $sparePartsRequest->getId();
        $addedParts = [];
        $deletedParts = [];
        foreach ($sparePartsRequest->getParts() as $part) {
            if (null === ($this->previousParts[$id][$part->partNumber] ?? null)) {
                $addedParts[] = $part;
            }
        }
        foreach ($sparePartsRequest->getDeletedParts() as $part) {
            if (false === ($this->previousParts[$id][$part->partNumber] ?? null)) {
                $deletedParts[] = $part;
            }
        }

        if ([] !== $addedParts || [] !== $deletedParts) {
            $notifier->notifyPartsUpdate($sparePartsRequest, $addedParts, $deletedParts);
        }

        if ($sparePartsRequest instanceof SBSparePartsRequest) {
            $this->serviceLocator->get(SparePartsRequestManager::class)->handleSBSparePartsRequestClosing($sparePartsRequest, $previousData->getStatus());
        }
    }

    public function afterSparePartsRequestItemRead(RequestEvent $event)
    {
        $request = $event->getRequest();
        $sparePartsRequest = $request->attributes->get('data');

        if (!$sparePartsRequest instanceof SparePartsRequest || !$request->isMethod(Request::METHOD_GET)) {
            return;
        }

        $this->populateSparePartsRequestFromION($sparePartsRequest);
    }

    public function afterSparePartsRequestCollectionRead(RequestEvent $event)
    {
        $request = $event->getRequest();
        $context = $request->attributes->get('_api_normalization_context', []);

        if (
            !($context['operation'] ?? null) instanceof GetCollection
            || !$request->isMethod(Request::METHOD_GET)
            || !\in_array('part', $context[AbstractNormalizer::GROUPS] ?? [], true)
            || !\in_array($context['resource_class'] ?? null, [SBSparePartsRequest::class, TOCSparePartsRequest::class], true)
        ) {
            return;
        }

        /** @var SparePartsRequest $sparePartsRequest */
        foreach ($request->attributes->get('data', []) as $sparePartsRequest) {
            $this->populateSparePartsRequestFromION($sparePartsRequest);
        }
    }

    public function afterSparePartsRequestDeserialize(RequestEvent $event)
    {
        $request = $event->getRequest();
        $sparePartsRequest = $request->attributes->get('data');

        if (!$sparePartsRequest instanceof TOCSparePartsRequest || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        $type = match ($sparePartsRequest->type) {
            TechnicianOnCallType::NOT_DEFINE_YET => SparePartsRequest::TYPE_UNDEFINED,
            TechnicianOnCallType::CUSTOMER => SparePartsRequest::TYPE_PAYABLE_SERVICES,
            TechnicianOnCallType::SSO => SparePartsRequest::TYPE_SSO,
            TechnicianOnCallType::FACTORY => SparePartsRequest::TYPE_WARRANTY,
            default => $sparePartsRequest->type,
        };

        $sparePartsRequest->type = $type;
    }

    public static function getSubscribedServices(): array
    {
        return [
            SparePartsRequestManager::class,
            SparePartsRequestNotifier::class,
            EntityManagerInterface::class,
            CachedIONItemDataProvider::class,
            CachedIONCollectionDataProvider::class,
            SiteToLocationConverter::class,
            ResourceMetadataCollectionFactoryInterface::class,
        ];
    }

    public function onDeliveryAddressUpdate(ViewEvent $event): void
    {
        $sparePartsRequest = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$sparePartsRequest instanceof SparePartsRequest || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        /** @var SparePartsRequest $previousSparePartsRequest */
        $previousSparePartsRequest = $request->attributes->get('previous_data');

        if ($previousSparePartsRequest->getDeliveryAddress()->getId() === $sparePartsRequest->getDeliveryAddress()->getId()) {
            return;
        }

        $this->serviceLocator->get(SparePartsRequestNotifier::class)->notifyDeliveryAddressUpdate($sparePartsRequest);
    }

    private function populateSparePartsRequestFromION(SparePartsRequest $sparePartsRequest)
    {
        if ($sparePartsRequest->getParts()->isEmpty()) {
            return;
        }

        if (null === $sparePartsRequest->salesOrder) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        /** @var ResourceMetadataCollection $shipmentMetadata */
        $shipmentMetadata = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class)->create(Shipment::class);
        /** @var ResourceMetadataCollection $salesOrderMetadata */
        $salesOrderMetadata = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class)->create(SalesOrder::class);

        /** @var Shipment[] $shipments */
        $shipments = $this->serviceLocator->get(CachedIONCollectionDataProvider::class)->provide($shipmentMetadata->getOperation(forceCollection: true), [], ShipmentOrderFilter::generateContext($sparePartsRequest->salesOrder));
        try {
            /** @var SalesOrder|null $salesOrder */
            $salesOrder = $this->serviceLocator->get(CachedIONItemDataProvider::class)->provide($salesOrderMetadata->getOperation(), ['salesOrder' => $sparePartsRequest->salesOrder]);
            if (null !== $salesOrder) {
                $sparePartsRequest->processSalesOrder($salesOrder, $this->serviceLocator->get(SiteToLocationConverter::class));
            }
        } catch (SoapException $exception) {
            // do nothing
        }

        foreach ($shipments as $shipment) {
            $sparePartsRequest->processShipment($shipment);
        }

        if ($sparePartsRequest->isFullyShipped() && \in_array($sparePartsRequest->getStatus(), [SparePartsRequest::STATUS_PENDING, SparePartsRequest::STATUS_OPEN], true)) {
            $sparePartsRequest->setStatus(SparePartsRequest::STATUS_SHIPPED);
            $entityManager->persist($sparePartsRequest);
        }

        $entityManager->flush();
    }
}
