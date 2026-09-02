<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Customer;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Sales\Customer;
use App\Entity\Sales\SecondarySalesRepresentative;
use App\Manager\Sales\CustomerManager;
use App\Message\Sales\CustomerMainRepresentativeUpdate;
use App\Notifier\Sales\Customer\CustomerNotifier;
use App\Repository\Sales\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CustomerUpdateListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function createSequenceWhenCustomerIsPending(ViewEvent $event)
    {
        /** @var Customer $customer */
        $customer = $event->getControllerResult();
        $request = $event->getRequest();

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if (!$customer instanceof Customer
            || !$request->isMethod(Request::METHOD_PUT)
            || 'customer_status' !== $operation->getName()) {
            return;
        }

        if (Customer::APPROVED !== $customer->getStatus() && null !== $customer->getValidatedAt()) {
            $customer->setValidatedAt(null);
            $em = $this->serviceLocator->get(EntityManagerInterface::class);
            $em->persist($customer);
            $em->flush();
        }

        if (Customer::PENDING === $customer->getStatus()) {
            $this->serviceLocator->get(CustomerManager::class)->createAndSendValidationSequence($customer);
        }
    }

    public function onCustomerEdition(ViewEvent $event): void
    {
        $customer = $event->getControllerResult();

        if (!$customer instanceof Customer || !$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $uow = $entityManager->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($customer);
        $changeSetMainContact = null !== $customer->getMainSalesRepresentative() ? $uow->getEntityChangeSet($customer->getMainSalesRepresentative()) : [];

        foreach ($changeSet as $field => [$before, $after]) {
            if ('inforLnBusinessPartnerCodes' !== $field) {
                $changeSet[$field] = [(string) $before, (string) $after];
            } else {
                $changeSet[$field] = [implode(', ', $before), implode(', ', $after)];
            }
        }

        if (null === ($user = $this->serviceLocator->get(Security::class)->getUser())) {
            return;
        }

        /** @var CustomerRepository $customerRepository */
        $customerRepository = $entityManager->getRepository(Customer::class);

        if ($mainContact = ($changeSetMainContact['asm'] ?? false)) {
            $iriConverter = $this->serviceLocator->get(IriConverterInterface::class);
            $message = new CustomerMainRepresentativeUpdate($iriConverter->getIriFromResource($customer), $iriConverter->getIriFromResource($mainContact[1]));
            $this->serviceLocator->get(MessageBusInterface::class)->dispatch($message);
        }

        $secondarySalesRepresentatives = [];
        $previousSalesRepresentatives = [];
        foreach ($customerRepository->getActualSecondarySalesRepresentatives($customer) as $actualSecondarySalesRepresentative) {
            $previousSalesRepresentatives[\sprintf('%s-%s', $actualSecondarySalesRepresentative['asm_id'], $actualSecondarySalesRepresentative['subDivision_id'])] = $actualSecondarySalesRepresentative;
        }

        foreach ($customer->getSecondarySalesRepresentatives() as $secondarySalesRepresentative) {
            $secondarySalesRepresentatives[\sprintf('%s-%s', $secondarySalesRepresentative->asm->getId(), $secondarySalesRepresentative->subDivision->getId())] = $secondarySalesRepresentative;
        }

        $salesRepresentativeChangeSet = [];
        /** @var SecondarySalesRepresentative $addedSecondarySalesRepresentative */
        foreach (array_diff_key($secondarySalesRepresentatives, $previousSalesRepresentatives) as $addedSecondarySalesRepresentative) {
            $salesRepresentativeChangeSet[] = \sprintf('%s added as secondary contact point', (string) $addedSecondarySalesRepresentative);
        }

        foreach (array_diff_key($previousSalesRepresentatives, $secondarySalesRepresentatives) as $removedSecondarySalesRepresentative) {
            $salesRepresentativeChangeSet[] = \sprintf('ASM: %s %s / SubDivision: %s removed as secondary contact point', $removedSecondarySalesRepresentative['lastname'], $removedSecondarySalesRepresentative['firstname'], $removedSecondarySalesRepresentative['subDivision_name']);
        }

        if (empty($changeSet) && [] === $salesRepresentativeChangeSet) {
            return;
        }

        $this->serviceLocator->get(CustomerNotifier::class)->sendEdition($customer, $user, $changeSet, $salesRepresentativeChangeSet);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onCustomerEdition', EventPriorities::PRE_WRITE],
                ['createSequenceWhenCustomerIsPending', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            CustomerManager::class,
            EntityManagerInterface::class,
            CustomerNotifier::class,
            Security::class,
            MessageBusInterface::class,
            IriConverterInterface::class,
        ];
    }
}
