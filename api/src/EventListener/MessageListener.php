<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onMessage(MessageEvent $event)
    {
        $message = $event->getMessage();
        if (!$message instanceof Email) {
            return;
        }
        if ([] === $message->getFrom()) {
            $message->from(new Address($this->serviceLocator->get(ParameterBagInterface::class)->get('notify_mail_sender')));
        }

        $tos = array_unique(array_map($this->stringifyAddress(...), $message->getTo()));
        $ccs = array_filter(array_unique(array_map($this->stringifyAddress(...), $message->getCc())), static fn (string $email) => !\in_array($email, $tos, true));
        $bccs = array_filter(array_unique(array_map($this->stringifyAddress(...), $message->getBcc())), static fn (string $email) => !\in_array($email, [...$tos, ...$ccs], true));

        $message->to(...$tos)->cc(...$ccs)->bcc(...$bccs);

        if (!$message instanceof TemplatedEmail) {
            $this->log($message);

            return;
        }
        $parameters = [];
        foreach ($message->getContext() as $key => $value) {
            if (!\is_string($value) && !is_numeric($value)) {
                continue;
            }
            $parameters['%'.$key.'%'] = (string) $value;
        }

        $message->subject($this->serviceLocator->get(TranslatorInterface::class)->trans($message->getSubject(), $parameters, 'emails', $parameters['%locale%'] ?? null));

        $this->log($message);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => 'onMessage',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            ParameterBagInterface::class,
            TranslatorInterface::class,
            'monolog.logger.emails' => LoggerInterface::class,
        ];
    }

    private function log(Email $message)
    {
        $log = \sprintf('Email "%s" has been sent to %s from %s', $message->getSubject(), implode(', ', array_map($this->stringifyAddress(...), $message->getTo())), implode(', ', array_map($this->stringifyAddress(...), $message->getFrom())));

        if ([] !== $message->getCc()) {
            $log .= \sprintf(', copied to %s', implode(', ', array_map($this->stringifyAddress(...), $message->getCc())));
        }

        if ([] !== $message->getBcc()) {
            $log .= \sprintf(', blind copied to %s', implode(', ', array_map($this->stringifyAddress(...), $message->getBcc())));
        }

        $this->serviceLocator->get('monolog.logger.emails')->info($log);
    }

    private static function stringifyAddress(Address $address): string
    {
        return $address->toString();
    }
}
