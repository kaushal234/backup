<?php

declare(strict_types=1);

namespace App\Dto\DocumentTranslation;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class DeeplStatusResponse
{
    public const STATUS_DONE = 'done';
    public const STATUS_ERROR = 'error';

    #[Assert\NotBlank]
    public string $status;

    #[SerializedName('seconds_remaining')]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public ?int $secondsRemaining = null;

    public ?string $message = null;

    public function isDone(): bool
    {
        return self::STATUS_DONE === $this->status;
    }

    public function isError(): bool
    {
        return self::STATUS_ERROR === $this->status;
    }
}
