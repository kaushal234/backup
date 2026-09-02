<?php

declare(strict_types=1);

namespace App\Mailer\Sales;

use App\Entity\Directory\People;
use App\Entity\Sales\Order;
use App\Mailer\AbstractPoolMailer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class OrdersPoolMailer extends AbstractPoolMailer
{
    public function __construct(MailerInterface $mailer)
    {
        parent::__construct($mailer);
    }

    public function addOrder(Order $order, array $recipients, string $key): self
    {
        if ([] === $recipients) {
            return $this;
        }

        if (null !== $email = $this->getEmail($key)) {
            $context = $email->getContext();

            $context['orders'] = array_merge($context['orders'] ?? [], [$order]);
            $email->context($context);

            return $this;
        }

        $this->addEmail(
            (new TemplatedEmail())
                ->context(['orders' => [$order]])
                ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients)),
            $key
        );

        return $this;
    }
}
