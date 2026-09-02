<?php

declare(strict_types=1);

namespace LegacyBundle\Security\Http\Firewall;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Firewall as BaseFirewall;

class Firewall extends BaseFirewall
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 33],
            KernelEvents::FINISH_REQUEST => 'onKernelFinishRequest',
        ];
    }
}
