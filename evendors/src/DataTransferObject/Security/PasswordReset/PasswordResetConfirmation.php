<?php

declare(strict_types=1);

namespace App\DataTransferObject\Security\PasswordReset;

use Symfony\Component\Validator\Constraints as Assert;

final class PasswordResetConfirmation
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 15, max: 255)]
    public ?string $newPassword = null;
}
