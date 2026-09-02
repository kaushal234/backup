<?php

declare(strict_types=1);

namespace App\EventListener\Finance\InvoiceRecord;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Finance\AccountReceivable;
use App\Entity\Finance\InvoiceRecord;
use App\Repository\Finance\ExchangeRateRepository;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class InvoiceRecordWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onPostWrite(ViewEvent $event)
    {
        $invoiceRecord = $event->getControllerResult();

        if (!$invoiceRecord instanceof InvoiceRecord || !\in_array($event->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        if (null !== ($message = $invoiceRecord->comment)) {
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($invoiceRecord, $message);
        }
    }

    public function onPreWrite(ViewEvent $event)
    {
        $invoiceRecord = $event->getControllerResult();
        if (!$invoiceRecord instanceof InvoiceRecord || !\in_array($event->getRequest()->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT], true)) {
            return;
        }

        if (null !== $invoiceRecord->comment) {
            $invoiceRecord->lastComment = $invoiceRecord->comment;
        }

        if (null === $invoiceRecord->revisedDueDate || $invoiceRecord->revisedDueDate < new \DateTime('60 days ago')) {
            return;
        }

        try {
            $originalAmountConverted = $this->serviceLocator->get(ExchangeRateRepository::class)->convertAmount($invoiceRecord->originalAmount, $invoiceRecord->currency->getName());
        } catch (NoResultException $e) {
            return;
        }

        if ($originalAmountConverted < AccountReceivable::DELINQUENT_MINIMUM_WITH_PAST_DUE || $originalAmountConverted > AccountReceivable::DELINQUENT_MINIMUM) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $repository = $entityManager->getRepository(AccountReceivable::class);

        $accountReceivable = $repository->findOneBy(['erpInvoiceNumber' => $invoiceRecord->invoiceNumber, 'customerErpReference' => $invoiceRecord->customerErpReference]);
        if (!$accountReceivable instanceof AccountReceivable) {
            return;
        }

        $accountReceivable->delinquent = false;
        $entityManager->persist($accountReceivable);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPreWrite', EventPriorities::PRE_WRITE],
                ['onPostWrite', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            EntityManagerInterface::class,
            ExchangeRateRepository::class,
        ];
    }
}
