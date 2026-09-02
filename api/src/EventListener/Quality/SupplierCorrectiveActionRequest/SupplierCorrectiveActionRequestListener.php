<?php

declare(strict_types=1);

namespace App\EventListener\Quality\SupplierCorrectiveActionRequest;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Client\Exception\SoapException;
use App\Entity\AuthorizedApplication;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use App\Entity\Purchasing\VendorWarrantyClaimStatus;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Event\Activity\CommentCreatedEvent;
use App\Event\Activity\CommentPreCreateEvent;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\MasterData\EnterpriseModel\Employee;
use App\ION\Resources\MasterData\Items\Item;
use App\Notifier\Quality\SupplierCorrectiveActionRequest\SupplierCorrectiveActionRequestNotifier;
use App\Repository\Directory\PeopleRepository;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\ModLinkManager;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SupplierCorrectiveActionRequestListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentPreCreateEvent::class => ['onPreSupplierCorrectiveActionRequestComment'],
            CommentCreatedEvent::class => ['onPostSupplierCorrectiveActionRequestComment'],
            KernelEvents::VIEW => [
                ['onSupplierCorrectiveActionRequestCreation', EventPriorities::POST_WRITE],
                ['onSupplierCorrectiveActionRequestUpdate', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function onPreSupplierCorrectiveActionRequestComment(CommentPreCreateEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getItem();

        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest) {
            return;
        }

        $comment = $event->getComment();
        $security = $this->serviceLocator->get(Security::class);
        if (\in_array($supplierCorrectiveActionRequest->getStatus(), [SupplierCorrectiveActionRequest::CLOSED, SupplierCorrectiveActionRequest::VALIDATION], true)
            || (SupplierCorrectiveActionRequest::SCAR_COMMENT_DISCRIMINATOR === $comment->discriminator
                && (!$security->isGranted('SUPPLIER_CORRECTIVE_ACTION_REQUEST_COMMENT_VOTER', $supplierCorrectiveActionRequest)
                    // we do dot call the CommentVoter as it has been already called and returned true, so we only check instance of user
                    && !$security->getUser() instanceof AuthorizedApplication && !$security->getUser() instanceof VendorUser))) {
            throw new AccessDeniedHttpException();
        }
    }

    public function onPostSupplierCorrectiveActionRequestComment(CommentCreatedEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getItem();

        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest || !$event->isMainRequest()) {
            return;
        }

        $comment = $event->getComment();
        $file = null;
        if (!$comment->getFiles()->isEmpty()) {
            $file = $comment->getFiles()->last();
        }

        $newStatus = ($user = $this->serviceLocator->get(Security::class)->getUser()) instanceof People ? SupplierCorrectiveActionRequest::VENDOR_TO_FILL_FORM : SupplierCorrectiveActionRequest::TLD_TO_REVIEW_FORM;
        $supplierCorrectiveActionRequest->setStatus($newStatus);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->persist($supplierCorrectiveActionRequest);
        $entityManager->flush();

        $attachment = null !== $file ? new \SplFileInfo($this->serviceLocator->get(ParameterBagInterface::class)->get('legacy.upload_dir').\DIRECTORY_SEPARATOR.$file->getFilePath()) : null;
        $tos = \array_key_exists('tos', $metadata = $comment->metadata) ? $metadata['tos'] : [];
        $this->serviceLocator->get(SupplierCorrectiveActionRequestNotifier::class)->sendComment($supplierCorrectiveActionRequest, $user, $comment, $tos, $attachment);
    }

    public function onSupplierCorrectiveActionRequestCreation(ViewEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getControllerResult();

        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest || Request::METHOD_POST !== $event->getRequest()->getMethod()) {
            return;
        }

        $user = $this->serviceLocator->get(Security::class)->getUser();
        if ($user instanceof AuthorizedApplication && null !== $supplierCorrectiveActionRequest->posterInformation) {
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($supplierCorrectiveActionRequest, \sprintf('SCAR created from eVendor portal by %s', $supplierCorrectiveActionRequest->posterInformation));
        }

        if ($user instanceof AuthorizedApplication || $user instanceof VendorUser) {
            /** @var VendorWarrantyClaim $vendorWarrantyClaim */
            $vendorWarrantyClaim = $supplierCorrectiveActionRequest->getVendorWarrantyClaims()->first();
            $statusRepository = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(VendorWarrantyClaimStatus::class);
            /** @var VendorWarrantyClaimStatus $status */
            $status = $statusRepository->findoneBy(['name' => VendorWarrantyClaimStatus::VALIDATE_SCAR]);

            $vendorWarrantyClaim->status = $status;
        }

        $subscribers = [];
        $dataProvider = $this->serviceLocator->get(CachedIONItemDataProvider::class);
        /** @var ResourceMetadataCollection $metadata */
        $metadata = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class)->create(Item::class);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $entityManager->getRepository(People::class);
        foreach ($supplierCorrectiveActionRequest->getParts() as $part) {
            try {
                /** @var Item $item */
                $item = $dataProvider->provide($metadata->getOperation(), ['item' => $part->partNumber, 'site' => $supplierCorrectiveActionRequest->factory->getErp()]);
                /** @var Employee $employee */
                foreach ([$item->buyer, $item->planner] as $employee) {
                    if (null !== $employee) {
                        $subscribers[] = $peopleRepository->findOneBy(['email' => $employee->emailAddress]);
                    }
                }
            } catch (SoapException $exception) {
                // do nothing
            }
        }

        foreach ($supplierCorrectiveActionRequest->getAffectedFactories() as $affectedFactory) {
            $subscribers = [...$subscribers, ...$peopleRepository->findGroupsMembers(['ROLE_QAM', 'ROLE_QE'], $affectedFactory)];
        }

        // TODO define the rule when SCAR factory != BusinessPartnerDepartment collection, should we check all department and notify ?
        //        if ($supplierCorrectiveActionRequest->factory->getErp() !== $supplierCorrectiveActionRequest->getSupplierErp()) {
        //            $locationRepository = $entityManager->getRepository(Location::class);
        //            $location = $locationRepository->findOneBy(['erp' => $supplierCorrectiveActionRequest->getSupplierErp()]);
        //            $subscribers = [...$subscribers, ...$peopleRepository->findGroupsMembers(['ROLE_QAM', 'ROLE_MLM', 'GG_QUALITY'], $location)];
        //        }

        $iriConverter = $this->serviceLocator->get(IriConverterInterface::class);
        $alreadySubscribed = [];
        $subscribers = array_filter($subscribers);
        foreach ($subscribers as $subscriber) {
            if (\in_array($subscriber, $alreadySubscribed, true)) {
                continue;
            }
            $subscription = (new Subscription())
                ->setResource($iriConverter->getIriFromResource($supplierCorrectiveActionRequest))
                ->setUser($subscriber)
            ;

            $entityManager->persist($subscription);
            $alreadySubscribed[] = $subscriber;
        }

        $entityManager->flush();

        if (null !== ($nonConformity = $supplierCorrectiveActionRequest->nonConformity)) {
            $this->serviceLocator->get(ModLinkManager::class)->createLink($supplierCorrectiveActionRequest->getId(), 'SCAR', $nonConformity->getId(), 'NCR');
        }
    }

    public function onSupplierCorrectiveActionRequestUpdate(ViewEvent $event)
    {
        $supplierCorrectiveActionRequest = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$supplierCorrectiveActionRequest instanceof SupplierCorrectiveActionRequest || Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        /** @var SupplierCorrectiveActionRequest $previousObject */
        $previousObject = $request->attributes->get('previous_data');
        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if (SupplierCorrectiveActionRequest::CLOSED === $previousObject->getStatus() && 'update_supplier_corrective_action_request_status' !== $operation->getName()) {
            throw new BadRequestHttpException("Closed SCAR can't be edited");
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            EntityManagerInterface::class,
            SupplierCorrectiveActionRequestNotifier::class,
            Security::class,
            ParameterBagInterface::class,
            CachedIONItemDataProvider::class,
            IriConverterInterface::class,
            ModLinkManager::class,
            ResourceMetadataCollectionFactoryInterface::class,
        ];
    }
}
