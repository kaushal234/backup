<?php

declare(strict_types=1);

namespace AppBundle\EventListener;

use AppBundle\Event\BaanDatabaseQueryEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaanDatabaseQueryEventListener implements EventSubscriberInterface
{
    private readonly TranslatorInterface $translator;
    private bool $alreadyNotified = false;
    private readonly bool $baanNotificationEnabled;
    private readonly RequestStack $requestStack;

    // This is the whitelisted routes and their arguments to prevent displaying a notice to the user for migrated routes
    private array $migratedRoutes = [
        'legacy_manual_download_cd' => [],
    ];

    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, bool $baanNotificationEnabled)
    {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->baanNotificationEnabled = $baanNotificationEnabled;
    }

    public static function getSubscribedEvents(): array
    {
        return [BaanDatabaseQueryEvent::class => 'onBaanDatabaseQuery'];
    }

    public function onBaanDatabaseQuery(BaanDatabaseQueryEvent $event): void
    {
        if ($this->alreadyNotified || !$this->baanNotificationEnabled) {
            $event->stopPropagation();

            return;
        }

        $session = $this->requestStack->getSession();
        if (!$session instanceof Session) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (
            null !== $request
            && null !== ($matchingRouteArguments = ($this->migratedRoutes[$request->attributes->get('_route')] ?? null))
            && $matchingRouteArguments === array_intersect(array_merge($request->query->all(), $request->request->all()), $matchingRouteArguments)
        ) {
            return;
        }

        $this->alreadyNotified = true;

        $session->getFlashBag()->add('warning', $this->translator->trans('baan.queries', [], 'baan'));
    }
}
