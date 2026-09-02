<?php

declare(strict_types=1);

namespace App\EventListener\Purchasing\SupplierRanking;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Doctrine\EventListener\ActivityListener;
use App\Entity\Purchasing\Supplier\Supplier;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Gedmo\Blameable\BlameableListener;
use Gedmo\Timestampable\TimestampableListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * SupplierListener is responsible for handling supplier-related updates
 * and managing statuses of supplier rankings.
 */
class SupplierListener implements EventSubscriberInterface
{
    protected EntityRepository $supplierRankingRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        $this->supplierRankingRepository = $entityManager->getRepository(SupplierRanking::class);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['updateRanking', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function updateRanking(ViewEvent $event): void
    {
        $supplier = $event->getControllerResult();

        if (!$supplier instanceof Supplier) {
            return;
        }

        $supplierRanking = $this->supplierRankingRepository->findOneBy(['supplier' => $supplier]);

        // Create a new supplier ranking if not exist.
        if (null === $supplierRanking) {
            $supplierRanking = new SupplierRanking();
            $supplierRanking->supplier = $supplier;
        }

        $this->removeGedmoListener();

        // Disable supplier ranking if supplier has no Master BU or is not active.
        // lastReviewBy set to null to not assign authorized app with blameable.
        if ((null === $supplier->location && null === $supplierRanking->disabledAt)
            || Supplier::INACTIVE === $supplier->status
            || Supplier::DELETED === $supplier->status) {
            $supplierRanking->disabledAt = new \DateTime();
        }

        // Or re-enable
        if (null !== $supplierRanking->disabledAt
            && null !== $supplier->location
            && Supplier::ACTIVE === $supplier->status) {
            $supplierRanking->disabledAt = null;
        }

        // Prevent error on null legacy ID.
        if (null === $supplierRanking->getLegacyId()) {
            $supplierRanking->setLegacyId(0);
        }

        $this->entityManager->persist($supplierRanking);
        $this->entityManager->flush();
    }

    protected function removeGedmoListener(): void
    {
        $eventManager = $this->entityManager->getEventManager();
        foreach ($eventManager->getListeners('onFlush') as $listener) {
            if ($listener instanceof TimestampableListener || $listener instanceof ActivityListener) {
                $eventManager->removeEventSubscriber($listener);
            }
            if ($listener instanceof BlameableListener) {
                $eventManager->removeEventListener('onFlush', $listener);
            }
        }
    }
}
