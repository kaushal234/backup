<?php

declare(strict_types=1);

namespace App\Command\Sales;

use App\Mailer\Sales\OrdersPoolMailer;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Sales\OrderRepository;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsCommand(name: 'tld:notifications:sor:orders_pending')]
class PendingOrdersNotificationCommand extends Command
{
    private readonly PeopleRepository $peopleRepository;

    private readonly OrderRepository $orderRepository;

    private readonly OrdersPoolMailer $ordersPoolMailer;

    private readonly ArrayAdapter $cache;

    public function __construct(PeopleRepository $peopleRepository, OrderRepository $orderRepository, OrdersPoolMailer $ordersPoolMailer)
    {
        parent::__construct();
        $this->setDescription('Send notifications for sales order in pending opened in the last 24hours');

        $this->peopleRepository = $peopleRepository;
        $this->orderRepository = $orderRepository;
        $this->ordersPoolMailer = $ordersPoolMailer;
        $this->cache = new ArrayAdapter(0, false);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->orderRepository->findPendingOrders(new \DateTime('-1 day'));
        if ([] === $orders) {
            return 0;
        }

        foreach ($orders as $order) {
            $value = $this->cache->get($ssoId = (string) $order->getSso()->getId(), fn (ItemInterface $item) => $this->peopleRepository->findGroupMembers('ROLE_SA', $order->getSso()));
            $this->ordersPoolMailer->addOrder($order, $value, $ssoId);
        }

        $this->ordersPoolMailer->send('sor.pending_summary.message', 'Emails/Sales/Order/order_notify_pending_summary.html.twig');

        return 0;
    }
}
