<?php

declare(strict_types=1);

namespace App\ION\EventListener;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Activity\Comment;
use App\Entity\User;
use App\ION\Notifier\PurchaseOrderNotifier;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use App\ION\Resources\Procurement\Orders\PurchaseOrderLine;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class PurchaseOrderDataPersisterEventListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['sendMail', EventPriorities::POST_WRITE],
        ];
    }

    public function sendMail(ViewEvent $event): void
    {
        $object = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$object instanceof PurchaseOrder || !$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        /** @var PurchaseOrder $updatedOrder */
        $updatedOrder = $request->attributes->get('data');

        foreach ($updatedOrder->getLines() as $line) {
            $updatedLine = $object->getLineByKey($line->lineIdentifier, $line->sequence);
            if (!$updatedLine instanceof PurchaseOrderLine) {
                throw new BadRequestException(\sprintf('Purchase Order Line with identifier `%s` and sequence `%s` is not found', $line->lineIdentifier, $line->sequence));
            }
            if ((null !== $updatedLine->rescheduledDate && $updatedLine->confirmedSupplierDate > $updatedLine->rescheduledDate) || $updatedLine->confirmedSupplierDate > $updatedLine->plannedReceiptDate
            ) {
                $this->serviceLocator->get(PurchaseOrderNotifier::class)->notifyPurchaseOrderConfirmationDatesUpdate($object, $updatedOrder->editMessage);
                break;
            }
        }

        if (null !== $updatedOrder->editMessage && '' !== $updatedOrder->editMessage) {
            $comment = new Comment();
            /** @var IriConverterInterface $iriConverter */
            $iriConverter = $this->serviceLocator->get(IriConverterInterface::class);

            $comment
                ->setMessage($updatedOrder->editMessage)
                ->setResource($iriConverter->getIriFromResource($updatedOrder));
            $user = $this->serviceLocator->get(Security::class)->getUser();
            if ($user instanceof User) {
                $comment->setUser($user);
            }
            $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
            $entityManager->persist($comment);
            $entityManager->flush();
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            PurchaseOrderNotifier::class,
            Security::class,
            EntityManagerInterface::class,
            IriConverterInterface::class,
        ];
    }
}
