<?php

declare(strict_types=1);

namespace App\DataTable\Filter\EventListener;

use Kreyu\Bundle\DataTableBundle\Filter\Event\FilterEvents;
use Kreyu\Bundle\DataTableBundle\Filter\Event\PreHandleEvent;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransformDateRangeApiFilterData implements EventSubscriberInterface
{
    public function preHandle(PreHandleEvent $event): void
    {
        $data = $event->getData();
        $value = $data->getValue();

        $valueFrom = $value['from'] ?? null;
        $valueTo = $value['to'] ?? null;

        $data = clone $data;

        if ($valueFrom && $valueTo) {
            $data->setValue(['after' => $valueFrom, 'before' => $valueTo]);
            $data->setOperator(Operator::Between);
        } elseif ($valueFrom) {
            $data->setValue(['after' => $valueFrom]);
            $data->setOperator(Operator::GreaterThanEquals);
        } elseif ($valueTo) {
            $data->setValue(['before' => $valueTo]);
            $data->setOperator(Operator::LessThanEquals);
        }

        $event->setData($data);
    }

    public static function getSubscribedEvents(): array
    {
        return [FilterEvents::PRE_HANDLE => 'preHandle'];
    }
}
