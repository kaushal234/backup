<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SanitizedEmailListFactory
{
    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    /** @return list<string> */
    public function buildCleanEmailAddressList(array $recipients): array
    {
        $emails = [];

        foreach ($recipients as $recipient) {
            $email = match (true) {
                $recipient instanceof Address => $recipient->getAddress(),
                $recipient instanceof User => $recipient->getEmail(),
                \is_string($recipient) => $recipient,
                default => null,
            };

            if (null === $email) {
                continue;
            }

            $violations = $this->validator->validate($email, [
                new NotBlank(),
                new Email(mode: Email::VALIDATION_MODE_STRICT),
            ]);

            if (0 === $violations->count()) {
                $emails[] = $email;
            }
        }

        return array_values(array_unique($emails));
    }
}
