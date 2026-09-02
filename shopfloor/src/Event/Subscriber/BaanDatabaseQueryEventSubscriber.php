<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use App\Event\BaanDatabaseQueryEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaanDatabaseQueryEventSubscriber implements EventSubscriberInterface
{
    private $session;
    private $translator;
    private $alreadyNotified = false;

    public function __construct(SessionInterface $session, TranslatorInterface $translator)
    {
        $this->session = $session;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [BaanDatabaseQueryEvent::class => 'onBaanDatabaseQuery'];
    }

    public function onBaanDatabaseQuery(BaanDatabaseQueryEvent $event): void
    {
        if ($this->alreadyNotified || !$this->session instanceof Session) {
            $event->stopPropagation();

            return;
        }

        $this->session->getFlashBag()->add('baan.queries', $this->translator->trans('baan.queries', [], 'baan'));
        $this->alreadyNotified = true;
    }
}
