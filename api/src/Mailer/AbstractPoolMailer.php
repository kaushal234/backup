<?php

declare(strict_types=1);

namespace App\Mailer;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

abstract class AbstractPoolMailer
{
    protected ArrayCollection $emails;
    protected MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->emails = new ArrayCollection();
    }

    public function addEmail(TemplatedEmail $email, string|int|null $key = null): self
    {
        if (null !== $key) {
            $this->emails->set($key, $email);
        } else {
            $this->emails->add($email);
        }

        return $this;
    }

    public function getEmail($key): ?TemplatedEmail
    {
        return $this->emails->get($key);
    }

    public function countEmails(): int
    {
        return $this->emails->count();
    }

    public function send(string $subject, ?string $template = null, array $metadata = []): void
    {
        /** @var TemplatedEmail $email */
        foreach ($this->emails as $email) {
            $email->subject($subject);

            if (null !== $template) {
                $email->htmlTemplate($template);
            }

            $email->context(array_merge($email->getContext(), $metadata));
            $this->mailer->send($email);
        }

        $this->emails = new ArrayCollection();
    }
}
