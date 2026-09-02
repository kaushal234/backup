<?php

declare(strict_types=1);

namespace App\Notifier\Tasks;

use App\Entity\Directory\People;
use LegacyBundle\Model\Sequence;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SequenceNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {
    }

    public function sendEmail(Sequence $sequence, string $subject = 'task.sequence.subject_new', string $template = 'Emails/Task/new_sequence.html.twig', array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->to($sequence->getAssignee()->getEmail())
            ->addCc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $sequence->getCc()->toArray()))
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($this->buildContext($sequence, $context));

        $this->mailer->send($email);
    }

    private function buildContext(Sequence $sequence, array $context = []): array
    {
        return $context + [
            'sequenceDescription' => $sequence->getDescription(),
            'sequenceId' => (string) $sequence->getId(),
            'templateDescription' => $sequence->getTemplateDescription(),
        ];
    }
}
