<?php

declare(strict_types=1);

namespace ApiBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class AccountReceivableDueDateTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    final public const NOT_PAST_DUE = 'Not Past Due';

    /**
     * @var string
     */
    final public const PAST_DUE_30_DAYS = 'Past Due 0-30 days';

    /**
     * @var string
     */
    final public const PAST_DUE_60_DAYS = 'Past Due 31-60 days';

    /**
     * @var string
     */
    final public const PAST_DUE_90_DAYS = 'Past Due 61-90 days';

    /**
     * @var string
     */
    final public const PAST_DUE_180_DAYS = 'Past Due 91-180 days';

    /**
     * @var string
     */
    final public const PAST_DUE_MORE_THAN_180_DAYS = 'Past Due > 180 days';

    public function transform($value): mixed
    {
        return $value;
    }

    public function reverseTransform($value): mixed
    {
        $after = null;
        $before = null;
        switch ($value) {
            case self::NOT_PAST_DUE:
                $after = (new \DateTime())->format('Y-m-d');
                break;
            case self::PAST_DUE_30_DAYS:
                $before = (new \DateTime())->format('Y-m-d');
                $after = (new \DateTime('30 days ago'))->format('Y-m-d');
                break;
            case self::PAST_DUE_60_DAYS:
                $before = (new \DateTime('30 days ago'))->format('Y-m-d');
                $after = (new \DateTime('60 days ago'))->format('Y-m-d');
                break;
            case self::PAST_DUE_90_DAYS:
                $before = (new \DateTime('60 days ago'))->format('Y-m-d');
                $after = (new \DateTime('90 days ago'))->format('Y-m-d');
                break;
            case self::PAST_DUE_180_DAYS:
                $before = (new \DateTime('90 days ago'))->format('Y-m-d');
                $after = (new \DateTime('180 days ago'))->format('Y-m-d');
                break;
            case self::PAST_DUE_MORE_THAN_180_DAYS:
                $before = (new \DateTime('180 days ago'))->format('Y-m-d');
                break;
        }

        return [
            'after' => $after,
            'before' => $before,
        ];
    }
}
