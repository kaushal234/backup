<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Customer;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Notifier\Tasks\SequenceNotifier;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Model\Sequence;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CustomerCreationListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    /**
     * @var string
     */
    final public const SEQUENCE_TEMPLATE = 'sales.new.customer';
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function createSequenceWhenCustomerIsCreated(ViewEvent $event)
    {
        /** @var Customer $customer */
        $customer = $event->getControllerResult();

        $request = $event->getRequest();

        if (!$customer instanceof Customer || !$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        $route = $this->serviceLocator->get(RouterInterface::class)->generate('customer', ['id' => $customer->getId()], Router::ABSOLUTE_URL);

        /** @var People $assignee */
        $assignee = $this->serviceLocator->get(Security::class)->getUser();

        $sequence = new Sequence();
        $sequence
            ->setTemplateName(self::SEQUENCE_TEMPLATE)
            ->setCloseParams([
                'module' => 'SEQ',
                'assignor' => $assignee,
                'assignee' => $assignee,
                'cid' => $customer->getLegacyId(),
                'name' => $customer->getName(),
            ])
            ->setAssignee($assignee)
            ->setAssignor($assignee)
            ->setParentId($customer->getLegacyId())
            ->setLocation($assignee->getBusinessUnit()->getLocation())
            ->setDescription(
                \sprintf("New eCustomer Approval Process
                                %s (<a href='%s'>#%d</a>)",
                    $customer->getName(),
                    $route,
                    $customer->getLegacyId()
                )
            )
        ;

        try {
            $this->serviceLocator->get(SequenceManager::class)->insert($sequence);
        } catch (\Exception $exception) {
            throw new \LogicException('Sequence not inserted. Reason: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $context = [
            'customerName' => $customer->getName(),
            'customerId' => (string) $customer->getId(),
        ];

        $this->serviceLocator->get(SequenceNotifier::class)->sendEmail($sequence, 'customer.subject', 'Emails/Sales/Customer/new_customer_sequence.html.twig', $context);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [['createSequenceWhenCustomerIsCreated', EventPriorities::POST_WRITE - 1]],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            SequenceManager::class,
            Security::class,
            SequenceNotifier::class,
            RouterInterface::class,
        ];
    }
}
