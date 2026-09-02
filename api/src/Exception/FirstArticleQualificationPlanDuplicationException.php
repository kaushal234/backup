<?php

declare(strict_types=1);

namespace App\Exception;

use ApiPlatform\Metadata\ErrorResource;
use ApiPlatform\Metadata\Exception\ProblemExceptionInterface;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;

#[ErrorResource]
final class FirstArticleQualificationPlanDuplicationException extends \RuntimeException implements ProblemExceptionInterface
{
    public const string REASON_TARGET_HAS_PLAN = 'target_has_plan';
    public const string REASON_DIFFERENT_FACTORY = 'target_different_factory';

    /** @var list<array{faq: FirstArticleQualification, reason: string}> */
    public readonly array $rejections;

    public readonly int $statusCode;

    /** @param list<array{faq: FirstArticleQualification, reason: string}> $rejections */
    private function __construct(string $message, int $statusCode, array $rejections = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->rejections = array_values($rejections);
    }

    public static function sourceNotApproved(): self
    {
        return new self('Only approved qualification plans can be duplicated.', 409);
    }

    /** @param list<array{faq: FirstArticleQualification, reason: string}> $rejections */
    public static function targetsRejected(array $rejections): self
    {
        return new self('One or more target FAQs cannot receive the duplicated plan.', 422, $rejections);
    }

    // ProblemExceptionInterface
    public function getType(): string
    {
        return '/errors/first_article_qualification_plan_duplication';
    }

    public function getTitle(): string
    {
        return match ($this->statusCode) {
            409 => 'Source plan not approved',
            422 => 'Target FAQs rejected',
            default => 'Duplication failed',
        };
    }

    public function getStatus(): int
    {
        return $this->statusCode;
    }

    public function getDetail(): string
    {
        return $this->getMessage();
    }

    public function getInstance(): ?string
    {
        return null;
    }
}
