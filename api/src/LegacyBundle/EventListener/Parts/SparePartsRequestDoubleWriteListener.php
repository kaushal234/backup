<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener\Parts;

use ApiPlatform\Metadata\Post;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\EquipmentRecord;
use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\SparePartsRequestPart;
use App\Entity\Parts\TOCSparePartsRequest;
use Doctrine\DBAL\Connection;
use LegacyBundle\Event\UpdateEvent;
use LegacyBundle\Manager\SparePartsRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SparePartsRequestDoubleWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onUpdate(UpdateEvent $event)
    {
        $part = $event->getObject();
        if (!$part instanceof SparePartsRequestPart || null === ($event->getChangeSet()['deletedAt'] ?? null)) {
            return;
        }

        $this->container->get(SparePartsRequestManager::class)->handleWarrantyPartDoubleWriting($part->sparePartsRequest);
        $this->container->get(SparePartsRequestManager::class)->handlePartDeletion($part);
    }

    public function onSparePartsRequestCreation(ViewEvent $event): void
    {
        $sparePartsRequest = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$sparePartsRequest instanceof SBSparePartsRequest || !$request->isMethod(Request::METHOD_POST) || $sparePartsRequest->getEquipmentRecords()->isEmpty()) {
            return;
        }

        /** @var Connection $legacyConnection */
        $legacyConnection = $this->container->get('doctrine.dbal.legacy_connection');
        $sql = \sprintf(
            'UPDATE sb_lines SET spr_id = %d WHERE (parent_id = %d) AND (er_id IN (%s))',
            $sparePartsRequest->getLegacyId(),
            $sparePartsRequest->sbId,
            implode(', ', array_map(static fn (EquipmentRecord $equipmentRecord) => $equipmentRecord->getLegacyId(), $sparePartsRequest->getEquipmentRecords()->toArray()))
        );

        $legacyConnection->prepare($sql)->executeStatement();
    }

    public function onTocSparePartsRequestCreation(ViewEvent $event): void
    {
        $sparePartsRequest = $event->getControllerResult();
        $request = $event->getRequest();
        $operation = $request->attributes->get('_api_operation');

        if (!$sparePartsRequest instanceof TOCSparePartsRequest || !$operation instanceof Post) {
            return;
        }

        $legacyConnection = $this->container->get('doctrine.dbal.legacy_connection');
        $qb = $legacyConnection->createQueryBuilder();
        $qb
            ->update('toc')
            ->set('parts_added', ':parts_added')
            ->where($qb->expr()->eq('id', ':id'))
            ->setParameters([
                'id' => $sparePartsRequest->tocId,
                'parts_added' => true,
            ])
        ;

        $legacyConnection->prepare($qb->getSQL());

        $stmt = $legacyConnection->prepare($qb->getSQL());
        foreach ($qb->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->executeStatement();
        $this->container->get(SparePartsRequestManager::class)->handleWarrantyPartDoubleWriting($sparePartsRequest);
    }

    public function onTocSparePartsRequestUpdate(ViewEvent $event): void
    {
        $sparePartsRequest = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$sparePartsRequest instanceof TOCSparePartsRequest || !$request->isMethod(Request::METHOD_PUT) || SparePartsRequest::TYPE_WARRANTY !== $sparePartsRequest->type) {
            return;
        }

        $this->container->get(SparePartsRequestManager::class)->handleWarrantyPartDoubleWriting($sparePartsRequest);

        foreach ($sparePartsRequest->getDeletedParts() as $deletedPart) {
            $this->container->get(SparePartsRequestManager::class)->handlePartDeletion($deletedPart);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UpdateEvent::class => 'onUpdate',
            KernelEvents::VIEW => [
                ['onSparePartsRequestCreation', EventPriorities::POST_WRITE],
                ['onTocSparePartsRequestCreation', EventPriorities::POST_WRITE],
                ['onTocSparePartsRequestUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return ['doctrine.dbal.legacy_connection' => Connection::class, SparePartsRequestManager::class];
    }
}
