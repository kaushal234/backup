<?php

declare(strict_types=1);

namespace App\Entity;

interface SurveyTargetInterface
{
    public function getEmail(): string;

    public function getDisplayName(): string;
}
