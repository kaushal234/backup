<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Event\EntityChangeEvent;
use App\Manager\Service\CustomerServiceRecordsManager;
use App\Request\Activity\CommentRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CustomerServiceRecordLog implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityChangeEvent::class => 'propertiesChanges',
        ];
    }

    public function propertiesChanges(EntityChangeEvent $event): void
    {
        $change = $event->getChange();
        $object = $change->getEntity();

        if (!$object instanceof AbstractCustomerServiceRecord && !$object instanceof Intervention) {
            return;
        }

        $commentParts = $this->serviceLocator->get(CustomerServiceRecordsManager::class)->logToComment($change->getChangeSet(), $object);

        if (!empty($commentParts)) {
            $comment = implode("\n", $commentParts);

            $this->serviceLocator->get(CommentRequestManager::class)->insertComment(
                $object instanceof AbstractCustomerServiceRecord ? $object : $object->customerServiceRecord,
                $comment,
                null,
                [],
                'CSR'
            );
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            CustomerServiceRecordsManager::class,
            CommentRequestManager::class,
        ];
    }
}
