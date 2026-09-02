<?php

declare(strict_types=1);

namespace App\EventListener\Purchasing\VendorWarrantyClaim;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Client\Exception\SoapException;
use App\Entity\Directory\People;
use App\Entity\Purchasing\NCRVendorWarrantyClaim;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Purchasing\WCVendorWarrantyClaim;
use App\Event\Activity\CommentCreatedEvent;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\MasterData\Items\Item;
use App\Manager\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimManager;
use App\Notifier\Purchasing\VendorWarrantyClaim\VendorWarrantyClaimNotifier;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Entity\LegacyFile;
use LegacyBundle\Manager\ModLinkManager;
use LegacyBundle\Manager\TOCManager;
use LegacyBundle\Manager\WarrantyClaimManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class VendorWarrantyClaimListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => ['onVendorWarrantyClaimComment'],
            KernelEvents::VIEW => [
                ['preCreation', EventPriorities::PRE_VALIDATE],
                ['postCreation', EventPriorities::POST_WRITE],
                ['postUpdate', EventPriorities::POST_WRITE],
            ],
            KernelEvents::REQUEST => [
                ['postRead', EventPriorities::POST_READ],
            ],
        ];
    }

    public function onVendorWarrantyClaimComment(CommentCreatedEvent $event)
    {
        $vendorWarrantyClaim = $event->getItem();

        if (!$vendorWarrantyClaim instanceof VendorWarrantyClaim) {
            return;
        }

        $comment = $event->getComment();
        $user = $this->serviceLocator->get(Security::class)->getUser();
        switch (true) {
            case $user instanceof People:
                $recipients = $comment->metadata['recipients'] ?? [];
                $this->serviceLocator->get(VendorWarrantyClaimNotifier::class)->sendCommentFromAlvest($vendorWarrantyClaim, $comment, $recipients);
                break;
            case $user instanceof VendorUser:
                if (VendorWarrantyClaimStatus::VENDOR_TO_RESPOND === $vendorWarrantyClaim->status->name) {
                    $statusRepository = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(VendorWarrantyClaimStatus::class);
                    $status = $statusRepository->findOneBy(['name' => VendorWarrantyClaimStatus::REVIEW_VENDOR_RESPONSE]);
                    $vendorWarrantyClaim->status = $status;
                    $assignee = $this->serviceLocator->get(VendorWarrantyClaimManager::class)->getAssigneeOnStatusChange($vendorWarrantyClaim);
                    $vendorWarrantyClaim->assignee = $assignee;

                    $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
                    $entityManager->persist($vendorWarrantyClaim);
                    $entityManager->flush();

                    $this->serviceLocator->get(VendorWarrantyClaimNotifier::class)->sendStatus($vendorWarrantyClaim, VendorWarrantyClaimStatus::VENDOR_TO_RESPOND);
                }

                $this->serviceLocator->get(VendorWarrantyClaimNotifier::class)->sendCommentFromExternal($vendorWarrantyClaim, $comment);
                break;
            default:
                // do nothing
        }
    }

    public function preCreation(ViewEvent $event)
    {
        $vendorWarrantyClaim = $event->getControllerResult();

        if (!$vendorWarrantyClaim instanceof VendorWarrantyClaim || Request::METHOD_POST !== $event->getRequest()->getMethod()) {
            return;
        }

        $assignee = $this->serviceLocator->get(VendorWarrantyClaimManager::class)->getAssigneeFromParts($vendorWarrantyClaim);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $entityManager->getRepository(People::class);
        if (null === $assignee) {
            $mlms = $peopleRepository->findGroupMembers('ROLE_MLM', $vendorWarrantyClaim->location);
            $assignee = $mlms[0] ?? null;
        }

        $supplier = null;
        if (null !== $vendorWarrantyClaim->getSupplierNumber()) {
            $supplier = $this->serviceLocator->get(BusinessPartnerManager::class)->findSupplier($vendorWarrantyClaim->getSupplierNumber());
        }

        $statusRepository = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(VendorWarrantyClaimStatus::class);
        $status = $statusRepository->findOneBy(['name' => VendorWarrantyClaimStatus::PENDING]);

        if ($vendorWarrantyClaim instanceof WCVendorWarrantyClaim
            || (!$supplier instanceof BusinessPartner && null === $vendorWarrantyClaim->requestedCreditAmount)) {
            $status = $statusRepository->findOneBy(['name' => VendorWarrantyClaimStatus::QA_ANALYSIS]);
            $qams = $peopleRepository->findGroupMembers('ROLE_QAM', $vendorWarrantyClaim->location);
            $assignee = $qams[0] ?? null;
        }

        $vendorWarrantyClaim->assignee = $assignee;
        $vendorWarrantyClaim->status = $status;
    }

    public function postCreation(ViewEvent $event)
    {
        $vendorWarrantyClaim = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$vendorWarrantyClaim instanceof VendorWarrantyClaim || Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        $this->serviceLocator->get(VendorWarrantyClaimNotifier::class)->sendCreation($vendorWarrantyClaim);

        switch (true) {
            case $vendorWarrantyClaim instanceof WCVendorWarrantyClaim:
                $linkId = $vendorWarrantyClaim->warrantyClaimId;
                $module = 'WC';
                break;
            case $vendorWarrantyClaim instanceof NCRVendorWarrantyClaim:
                $linkId = $vendorWarrantyClaim->nonConformity->getId();
                $module = 'NCR';

                $nonConformity = $vendorWarrantyClaim->nonConformity;
                $nonConformity
                    ->setSupplierName($vendorWarrantyClaim->getSupplierName())
                    ->setSupplierNumber($vendorWarrantyClaim->getSupplierNumber())
                ;
                $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
                $entityManager->persist($nonConformity);
                $entityManager->flush();
                break;
            default:
                $linkId = null;
                $module = null;
        }

        if (null !== $vendorWarrantyClaim->supplierCorrectiveActionRequest) {
            $this->serviceLocator->get(ModLinkManager::class)->createLink($vendorWarrantyClaim->supplierCorrectiveActionRequest->getId(), 'SCAR', $vendorWarrantyClaim->getId(), 'VWC');
        }

        if (null !== $linkId && null !== $module) {
            $this->serviceLocator->get(ModLinkManager::class)->createLink($linkId, $module, $vendorWarrantyClaim->getId(), 'VWC');
        }
    }

    public function postUpdate(ViewEvent $event)
    {
        $vendorWarrantyClaim = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$vendorWarrantyClaim instanceof VendorWarrantyClaim || Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        /** @var VendorWarrantyClaim $previous */
        $previous = $request->attributes->get('previous_data');
        if (($previousStatus = $previous->status->name) !== $newStatus = $vendorWarrantyClaim->status->name) {
            if (VendorWarrantyClaimStatus::VENDOR_TO_RESPOND === $vendorWarrantyClaim->status->name) {
                $this->serviceLocator->get(VendorWarrantyClaimNotifier::class)->sendVendor($vendorWarrantyClaim);
            }
            $this->serviceLocator->get(VendorWarrantyClaimNotifier::class)->sendStatus($vendorWarrantyClaim, $previousStatus);

            if (\in_array($newStatus, VendorWarrantyClaimStatus::CLOSED_STATUSES, true)
            && !\in_array($previousStatus, VendorWarrantyClaimStatus::CLOSED_STATUSES, true)) {
                $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
                $vendorWarrantyClaim->closedAt = new \DateTime();
                $entityManager->persist($vendorWarrantyClaim);
                $entityManager->flush();
            }

            if (!\in_array($newStatus, VendorWarrantyClaimStatus::CLOSED_STATUSES, true)
                && \in_array($previousStatus, VendorWarrantyClaimStatus::CLOSED_STATUSES, true)) {
                $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
                $vendorWarrantyClaim->closedAt = null;
                $entityManager->persist($vendorWarrantyClaim);
                $entityManager->flush();
            }
        }
        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if (($previousAssignee = $previous->assignee) !== $vendorWarrantyClaim->assignee && !\in_array($operation->getName(), ['update_ncr_vendor_warranty_claim_status', 'update_wc_vendor_warranty_claim_status'], true)) {
            $this->serviceLocator->get(VendorWarrantyClaimNotifier::class)->sendAssignee($vendorWarrantyClaim, $previousAssignee);
        }

        if ($previous->supplierCorrectiveActionRequest !== ($supplierCorrectiveActionRequest = $vendorWarrantyClaim->supplierCorrectiveActionRequest) && null !== $supplierCorrectiveActionRequest) {
            $this->serviceLocator->get(ModLinkManager::class)->createLink($supplierCorrectiveActionRequest->getId(), 'SCAR', $vendorWarrantyClaim->getId(), 'VWC');
        }

        if ($vendorWarrantyClaim instanceof NCRVendorWarrantyClaim && $previous->getSupplierNumber() !== $vendorWarrantyClaim->getSupplierNumber()) {
            $nonConformity = $vendorWarrantyClaim->nonConformity;
            $nonConformity
                ->setSupplierName($vendorWarrantyClaim->getSupplierName())
                ->setSupplierNumber($vendorWarrantyClaim->getSupplierNumber())
            ;
            $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
            $entityManager->persist($nonConformity);
            $entityManager->flush();
        }
    }

    public function postRead(RequestEvent $event)
    {
        $vendorWarrantyClaim = $event->getRequest()->attributes->get('data');
        $request = $event->getRequest();

        $operation = $request->attributes->get('_api_operation');
        if ($vendorWarrantyClaim instanceof VendorWarrantyClaim && ($operation instanceof Get || $operation instanceof Put || $operation instanceof Post)) {
            if (null !== ($erp = $vendorWarrantyClaim->location->getErp())) {
                /** @var ResourceMetadataCollection $metadata */
                $metadata = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class)->create(Item::class);
                $dataProvider = $this->serviceLocator->get(CachedIONItemDataProvider::class);
                foreach ($vendorWarrantyClaim->getParts() as $part) {
                    try {
                        /** @var Item|null $item */
                        $item = $dataProvider->provide($metadata->getOperation(), ['item' => $part->partNumber, 'site' => $erp]);
                        $part->standardCost = (float) $item?->standardPrice;
                    } catch (SoapException) {
                    }
                }
            }
        }

        if (!$vendorWarrantyClaim instanceof WCVendorWarrantyClaim || !$operation instanceof Get) {
            return;
        }

        $user = $this->serviceLocator->get(Security::class)->getUser();
        $onlyPublic = $user instanceof VendorUser;

        if ($onlyPublic) {
            foreach ($vendorWarrantyClaim->getFiles() as $file) {
                if (!$file->isPublic()) {
                    $vendorWarrantyClaim->removeFile($file);
                }
            }
        }

        foreach ($this->serviceLocator->get(WarrantyClaimManager::class)->findFilesById($vendorWarrantyClaim->warrantyClaimId, $onlyPublic) as $warrantyClaimFile) {
            $file = new LegacyFile();
            $file->id = (int) $warrantyClaimFile['id'];
            $file->description = $warrantyClaimFile['description'];
            $file->createdAt = new \DateTime($warrantyClaimFile['date']);
            $file->filePath = \sprintf('warranty_files/%s', $warrantyClaimFile['filename']);

            $vendorWarrantyClaim->addWarrantyClaimFile($file);
        }
        foreach ($this->serviceLocator->get(TOCManager::class)->findPublicFilesofTOCByWCId($vendorWarrantyClaim->warrantyClaimId) as $tocFile) {
            $file = new LegacyFile();
            $file->id = (int) $tocFile['id'];
            $file->description = $tocFile['description'];
            $file->createdAt = new \DateTime($tocFile['date']);
            if (null !== ($tocFile['filepath'] ?? null)) {
                $originalPathParts = explode('/', (string) $tocFile['filepath']);
                $file->filePath = \sprintf('mod_files/%s', end($originalPathParts));
            }

            $vendorWarrantyClaim->addTocFile($file);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            VendorWarrantyClaimNotifier::class,
            BusinessPartnerManager::class,
            Security::class,
            VendorWarrantyClaimManager::class,
            WarrantyClaimManager::class,
            ModLinkManager::class,
            TOCManager::class,
            CachedIONItemDataProvider::class,
            ResourceMetadataCollectionFactoryInterface::class,
        ];
    }
}
