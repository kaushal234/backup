<?php

declare(strict_types=1);

namespace AppBundle\Manager\Quality\FirstArticleQualification;

final class FirstArticleQualificationStatus
{
    public const PENDING = 'PENDING';
    public const IN_PROGRESS = 'IN_PROGRESS';
    public const IN_PROGRESS_PLAN_COMPLETED = 'IN PROGRESS / PLAN 100% COMPLETED';
    public const CONDITIONAL = 'CONDITIONAL';
    public const QUALIFIED = 'QUALIFIED';
    public const REJECTED = 'REJECTED';

    public const APPROVED = 'APPROVED';
    public const UNAPPROVED = 'UNAPPROVED';
    public const NOT_APPROVED_YET = 'NOT_APPROVED_YET';

    public static function getStatuses()
    {
        return [
            self::PENDING => self::PENDING,
            self::IN_PROGRESS => self::IN_PROGRESS,
            self::IN_PROGRESS_PLAN_COMPLETED => self::IN_PROGRESS_PLAN_COMPLETED,
            self::CONDITIONAL => self::CONDITIONAL,
            self::QUALIFIED => self::QUALIFIED,
            self::REJECTED => self::REJECTED,
        ];
    }

    public static function getPlanStatuses()
    {
        return [
            self::APPROVED => self::APPROVED,
            self::UNAPPROVED => self::UNAPPROVED,
            self::NOT_APPROVED_YET => self::NOT_APPROVED_YET,
        ];
    }
}
