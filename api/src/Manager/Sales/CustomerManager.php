<?php

declare(strict_types=1);

namespace App\Manager\Sales;

use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\EventListener\Sales\Customer\CustomerCreationListener;
use App\Notifier\Tasks\SequenceNotifier;
use App\Repository\Directory\PeopleRepository;
use LegacyBundle\Manager\SequenceManager;
use LegacyBundle\Model\Sequence;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class CustomerManager
{
    /**
     * @var string
     */
    final public const SEQUENCE_TEMPLATE = 'sales.Customer.Validation';

    private readonly SequenceManager $sequenceManager;
    private readonly SequenceNotifier $notifier;
    private readonly Security $security;
    private readonly RouterInterface $router;
    private readonly PeopleRepository $repository;

    public function __construct(SequenceManager $sequenceManager, SequenceNotifier $notifier, Security $security, RouterInterface $router, PeopleRepository $repository)
    {
        $this->sequenceManager = $sequenceManager;
        $this->notifier = $notifier;
        $this->security = $security;
        $this->router = $router;
        $this->repository = $repository;
    }

    public function createAndSendValidationSequence(Customer $customer)
    {
        foreach ([self::SEQUENCE_TEMPLATE, CustomerCreationListener::SEQUENCE_TEMPLATE] as $template) {
            if (false !== $sequence = $this->sequenceManager->findOpenSequence($template, $customer->getLegacyId())) {
                throw new ConflictHttpException(\sprintf('Sequence #%s already opened for this eCustomer', $sequence['id']));
            }
        }

        $assignee = $this->security->getUser() ?? (null !== ($salesRepresentative = $customer->getMainSalesRepresentative()) ? $salesRepresentative->asm : null);

        if (!$assignee instanceof People) {
            /** @var People[] $salesAdmins */
            $salesAdmins = $this->repository->findGroupMembers('ROLE_SA');
            $assignee = $salesAdmins[0];
        }

        $route = $this->router->generate('customer', ['id' => $customer->getId()], Router::ABSOLUTE_URL);
        $sequence = new Sequence();

        $sequence
            ->setTemplateName(self::SEQUENCE_TEMPLATE)
            ->setCloseParams([
                'module' => 'SEQ',
                'assignor' => $assignee,
                'assignee' => $assignee,
            ])
            ->setAssignee($assignee)
            ->setAssignor($assignee)
            ->setParentId($customer->getLegacyId())
            ->setLocation($assignee->getBusinessUnit()->getLocation())
            ->setDescription(
                \sprintf("Customer %s (<a href='%s'>#%d</a>) has to be validated",
                    $customer->getName(),
                    $route,
                    $customer->getLegacyId()
                )
            )
        ;

        try {
            $this->sequenceManager->insert($sequence);
        } catch (\Exception $exception) {
            throw new \LogicException('Sequence not inserted. Reason: '.$exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->notifier->sendEmail($sequence, 'customer.validation.subject', 'Emails/Sales/Customer/validation_customer_sequence.html.twig');
    }
}
