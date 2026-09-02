<?php

declare(strict_types=1);

namespace App\DataTransferObject\Security\PasswordReset;

use Symfony\Component\Validator\Constraints as Assert;

final class PasswordResetRequest
{
    #[Assert\Email(message: 'security.password_reset.form.email.invalid', mode: Assert\Email::VALIDATION_MODE_HTML5)]
    public string $email;
}
