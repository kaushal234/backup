<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Entity\Directory\People;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ErrorsPoolMailer extends AbstractPoolMailer
{
    public function addError(People $people, string $message, ?string $link = null)
    {
        $error = [
            'message' => $message,
            'link' => $link,
        ];

        if (null !== $email = $this->getEmail($people->getId())) {
            $context = $email->getContext();

            $context['errors'] = array_merge($context['errors'] ?? [], [$error]);
            $email->context($context);

            return $this;
        }

        $this->addEmail(
            (new TemplatedEmail())
                ->to($people->getEmail())
                ->htmlTemplate('Emails/Generic/errors.html.twig')
                ->context(['errors' => [$error], 'people', $people]),
            $people->getId()
        );
    }
}
