<?php

declare(strict_types=1);

namespace App\AI\Dto\Service\TechnicianOnCall;

final readonly class SurveyModel
{
    public function __construct(
        public int $execution,
        public int $responsiveness,
        public int $communication,
        public int $attitude,
        public string $comment,
    ) {
    }
}
