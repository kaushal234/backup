<?php

declare(strict_types=1);

namespace App\Notifier\Parts;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\EquipmentRecord;
use App\Entity\Parts\Part;
use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\TOCSparePartsRequest;
use App\ION\DataProvider\CachedIONCollectionDataProvider;
use App\ION\Filter\Warehousing\Shipments\ShipmentOrderFilter;
use App\ION\Resources\Warehousing\Shipments\Shipment;
use App\Manager\Service\TechnicianOnCallManager;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SparePartsRequestNotifier implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function notifyCreation(SparePartsRequest $sparePartsRequest): void
    {
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $sparePartsRequest->getEquipmentRecords()->first();
        if (null === ($sphEmail = $equipmentRecord->getSalesOrganisationService()?->getContact()?->getPartsCustomerSupportEmail())) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($sphEmail)
            ->subject('spr.subject.creation')
            ->htmlTemplate('Emails/SparePartsRequest/creation.html.twig')
            ->context($this->buildContext($sparePartsRequest));

        $this->addExtraRecipients($sparePartsRequest, $email);

        $this->container->get(MailerInterface::class)->send($email);
    }

    public function notifyPartsUpdate(SparePartsRequest $sparePartsRequest, array $addedParts, array $removedParts): void
    {
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $sparePartsRequest->getEquipmentRecords()->first();
        if (null === ($sphEmail = $equipmentRecord->getSalesOrganisationService()?->getContact()?->getPartsCustomerSupportEmail())) {
            return;
        }

        $context = $this->buildContext($sparePartsRequest);
        $context['addedParts'] = [];
        $context['removedParts'] = [];

        /** @var Part $part */
        foreach ($addedParts as $part) {
            $context['addedParts'][] = ['partNumber' => $part->partNumber, 'quantity' => $part->quantity, 'unitOfMeasure' => $part->unitOfMeasure];
        }

        /** @var Part $part */
        foreach ($removedParts as $part) {
            $context['removedParts'][] = ['partNumber' => $part->partNumber, 'quantity' => $part->quantity, 'unitOfMeasure' => $part->unitOfMeasure];
        }

        $email = (new TemplatedEmail())
            ->to($sphEmail)
            ->subject('spr.subject.parts_update')
            ->htmlTemplate('Emails/SparePartsRequest/update_parts.html.twig')
            ->context($context);

        $this->addExtraRecipients($sparePartsRequest, $email);

        $this->container->get(MailerInterface::class)->send($email);
    }

    public function notifyDeliveryAddressUpdate(SparePartsRequest $sparePartsRequest): void
    {
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $sparePartsRequest->getEquipmentRecords()->first();
        if (null === ($sphEmail = $equipmentRecord->getSalesOrganisationService()?->getContact()?->getPartsCustomerSupportEmail())) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($sphEmail)
            ->subject('spr.subject.delivery_address_update')
            ->htmlTemplate('Emails/SparePartsRequest/update_delivery_address.html.twig')
            ->context($this->buildContext($sparePartsRequest));

        $this->addExtraRecipients($sparePartsRequest, $email);

        $this->container->get(MailerInterface::class)->send($email);
    }

    public function notifyShipping(SparePartsRequest $sparePartsRequest): void
    {
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $sparePartsRequest->getEquipmentRecords()->first();
        if (null === ($sphEmail = $equipmentRecord->getSalesOrganisationService()?->getContact()?->getPartsCustomerSupportEmail())) {
            return;
        }

        /** @var ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory */
        $resourceMetadataFactory = $this->container->get(ResourceMetadataCollectionFactoryInterface::class);
        $metadata = $resourceMetadataFactory->create(Shipment::class);
        $shipments = null === $sparePartsRequest->salesOrder ? [] : $this->container->get(CachedIONCollectionDataProvider::class)->provide(
            $metadata->getOperation(forceCollection: true),
            [],
            ShipmentOrderFilter::generateContext($sparePartsRequest->salesOrder)
        );

        $trackingNumbers = [];

        /** @var Shipment $shipment */
        foreach ($shipments as $shipment) {
            if (null === $shipment->load->trackingNumber) {
                continue;
            }
            $trackingNumbers[$shipment->load->trackingNumber] = null !== $shipment->load->carrier ? $shipment->load->carrier->name : 'Unknown Carrier';
        }

        $email = (new TemplatedEmail())
            ->to($sphEmail)
            ->subject('spr.subject.shipped')
            ->htmlTemplate('Emails/SparePartsRequest/shipped.html.twig')
            ->context($this->buildContext($sparePartsRequest) + ['trackingNumbers' => $trackingNumbers]);

        $this->container->get(MailerInterface::class)->send($email);

        if (null === $contact = $sparePartsRequest->getDeliveryAddress()->contact) {
            return;
        }

        $email = (new TemplatedEmail())
            ->from($sphEmail)
            ->to($contact->getEmail())
            ->subject('spr.subject.shipped_customer')
            ->htmlTemplate('Emails/SparePartsRequest/shipped_customer.html.twig')
            ->context($this->buildContext($sparePartsRequest) + ['trackingNumbers' => $trackingNumbers]);

        $this->container->get(MailerInterface::class)->send($email);
    }

    public static function getSubscribedServices(): array
    {
        return [
            NormalizerInterface::class,
            MailerInterface::class,
            TechnicianOnCallManager::class,
            ResourceMetadataCollectionFactoryInterface::class,
            CachedIONCollectionDataProvider::class,
        ];
    }

    private function addExtraRecipients(SparePartsRequest $sparePartsRequest, Email $email): void
    {
        if ($sparePartsRequest instanceof SBSparePartsRequest) {
            if ('' !== ($factoryEmail = (string) $sparePartsRequest->factory->getContact()->getPartsCustomerSupportEmail())) {
                $email->addCc($factoryEmail);
            }
            if ('' !== ($ssoEmail = (string) $sparePartsRequest->sso->getContact()->getPartsCustomerSupportEmail())) {
                $email->addCc($ssoEmail);
            }
        }

        if ($sparePartsRequest instanceof TOCSparePartsRequest) {
            if (null !== $tocAssignee = $sparePartsRequest->technicianOnCall->assignee) {
                $email->addCc($tocAssignee->getEmail());
            }
            if (null !== $technicianEmail = $this->container->get(TechnicianOnCallManager::class)->getTechnicianEmail($sparePartsRequest->technicianOnCall)) {
                $email->addCc($technicianEmail);
            }
        }
    }

    private function buildContext(SparePartsRequest $sparePartsRequest): array
    {
        return [
            'sparePartsRequest' => $this->container->get(NormalizerInterface::class)->normalize($sparePartsRequest, 'jsonld', ['groups' => SparePartsRequest::ITEM_NORMALIZATION_GROUPS]),
            'id' => $sparePartsRequest->getId(),
        ];
    }
}
