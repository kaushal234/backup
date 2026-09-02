<?php

declare(strict_types=1);

namespace App\DataTransferObject\TechnicianOnCall;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTechnicianOnCallSurvey
{
    #[Assert\Range(min: 1, max: 5)]
    public int $execution;

    #[Assert\Range(min: 1, max: 5)]
    public int $responsiveness;

    #[Assert\Range(min: 1, max: 5)]
    public int $communication;

    #[Assert\Range(min: 1, max: 5)]
    public int $attitude;

    #[Assert\NotBlank]
    public string $comment;
}
