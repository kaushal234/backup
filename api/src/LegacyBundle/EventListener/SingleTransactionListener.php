<?php

declare(strict_types=1);

namespace LegacyBundle\EventListener;

use Doctrine\DBAL\Connection;
use LegacyBundle\Event\FlushEvent;
use LegacyBundle\Event\PostFlushEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SingleTransactionListener implements EventSubscriberInterface
{
    private readonly Connection $legacyConnection;

    private int $inProgressCount = 0;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function onTransactionStart()
    {
        if (0 === $this->inProgressCount) {
            $this->legacyConnection->beginTransaction();
        }

        ++$this->inProgressCount;
    }

    public function onTransactionFinish()
    {
        if (1 === $this->inProgressCount) {
            $this->legacyConnection->commit();
        }

        --$this->inProgressCount;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FlushEvent::class => 'onTransactionStart',
            PostFlushEvent::class => 'onTransactionFinish',
        ];
    }
}
