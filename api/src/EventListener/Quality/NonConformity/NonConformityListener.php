<?php

declare(strict_types=1);

namespace App\EventListener\Quality\NonConformity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Client\Exception\SoapException;
use App\Entity\Quality\NonConformity;
use App\Entity\Quality\NonQualityCost;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\MasterData\Items\Item;
use App\Notifier\Quality\NonConformity\NonConformityNotifier;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\ModLinkManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class NonConformityListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPostUpdate', EventPriorities::POST_WRITE],
                ['onPreCreate', EventPriorities::PRE_WRITE],
                ['onPostCreate', EventPriorities::POST_WRITE],
            ],
            KernelEvents::REQUEST => [
                ['onPostRead', EventPriorities::POST_READ],
            ],
        ];
    }

    public function onPostUpdate(ViewEvent $event): void
    {
        $nonConformity = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$nonConformity instanceof NonConformity || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        /** @var NonConformity $previousData */
        $previousData = $request->attributes->get('previous_data');
        if ($previousData->getSupplierNumber() !== $nonConformity->getSupplierNumber()) {
            $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
            foreach ($nonConformity->getVendorWarrantyClaims() as $vendorWarrantyClaim) {
                $vendorWarrantyClaim
                    ->setSupplierNumber($nonConformity->getSupplierNumber())
                    ->setSupplierName($nonConformity->getSupplierName())
                ;

                $entityManager->persist($vendorWarrantyClaim);
            }

            $entityManager->flush();
        }

        if (!\in_array($nonConformity->status, NonConformity::CLOSED_STATUSES, true) || $previousData->status === $nonConformity->status) {
            return;
        }

        $this->serviceLocator->get(NonConformityNotifier::class)->sendClosed($nonConformity, ['oldStatus' => $previousData->status]);
    }

    public function onPreCreate(ViewEvent $event): void
    {
        $nonConformity = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$nonConformity instanceof NonConformity || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        $nonConformity->currency = $nonConformity->location->getCurrency();

        $nonQualityCost = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(NonQualityCost::class)->findOneBy(['location' => $nonConformity->location]);

        if (!$nonQualityCost instanceof NonQualityCost) {
            return;
        }

        $nonConformity->nonQualityCost = $nonQualityCost->defaultCosts;
    }

    public function onPostCreate(ViewEvent $event): void
    {
        $nonConformity = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$nonConformity instanceof NonConformity || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        if (!$nonConformity->getCrabs()->isEmpty()) {
            foreach ($nonConformity->getCrabs() as $crab) {
                $this->serviceLocator->get(ModLinkManager::class)->createLink($crab->getId(), 'CRAB', $nonConformity->getId(), 'NCR');
            }
        }

        $this->serviceLocator->get(NonConformityNotifier::class)->sendCreation($nonConformity);
    }

    public function onPostRead(RequestEvent $event)
    {
        $nonConformity = $event->getRequest()->attributes->get('data');
        $request = $event->getRequest();

        $operation = $request->attributes->get('_api_operation');
        if ($nonConformity instanceof NonConformity && ($operation instanceof Get || $operation instanceof Put || $operation instanceof Post)) {
            if (null !== ($erp = $nonConformity->location->getErp())) {
                /** @var ResourceMetadataCollection $metadata */
                $metadata = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class)->create(Item::class);
                $dataProvider = $this->serviceLocator->get(CachedIONItemDataProvider::class);
                foreach ($nonConformity->getParts() as $part) {
                    try {
                        /** @var Item|null $item */
                        $item = $dataProvider->provide($metadata->getOperation(), ['item' => $part->partNumber, 'site' => $erp]);
                        $part->standardCost = (float) $item?->standardPrice;
                    } catch (SoapException) {
                    }
                }
            }
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            NonConformityNotifier::class,
            ModLinkManager::class,
            ResourceMetadataCollectionFactoryInterface::class,
            CachedIONItemDataProvider::class,
        ];
    }
}
