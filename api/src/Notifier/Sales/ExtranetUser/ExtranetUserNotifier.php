<?php

declare(strict_types=1);

namespace App\Notifier\Sales\ExtranetUser;

use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExtranetUserNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function sendPasswordUpdateLinkEmail(ExtranetUser $extranetUser): void
    {
        $email = (new TemplatedEmail())
            ->to($extranetUser->getEmail())
            ->subject('extranet_user.update_password.subject')
            ->htmlTemplate('Emails/Sales/ExtranetUser/extranetUserPasswordUpdateEmail.html.twig')
            ->context($this->buildContext($extranetUser))
        ;

        $this->mailer->send($email);
    }

    public function sendConfirmationEmail(ExtranetUser $extranetUser, People $people, string $subject, array $context = []): void
    {
        $context += [
            'user_email' => $people->getEmail(),
            'user_lastname' => $people->getLastname(),
            'user_firstname' => $people->getFirstname(),
            'user_location' => $people->getBusinessUnit()->getName(),
        ];

        $email = (new TemplatedEmail())
            ->to($extranetUser->getEmail())
            ->addCc($people->getEmail())
            ->subject($subject)
            ->htmlTemplate('Emails/Sales/ExtranetUser/extranetUserConfirmationEmail.html.twig')
            ->context($this->buildContext($extranetUser, $context))
        ;

        foreach ($context['ccs'] ?? [] as $cc) {
            $email->addCc($cc);
        }

        foreach ($context['bccs'] ?? [] as $bcc) {
            $email->addBcc($bcc);
        }
        $this->mailer->send($email);
    }

    public function sendContactEmail(ExtranetUser $extranetUser, string $message, string $to)
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->replyTo($extranetUser->getEmail())
            ->subject('extranet_user.contact.subject')
            ->htmlTemplate('Emails/Sales/ExtranetUser/extranet_contact.html.twig')
            ->context(['message' => $message, 'extranetUser' => $this->normalizer->normalize($extranetUser, null, ['groups' => ['extranet_user', 'extranet_user_public', 'user_profile', 'customer_list']])]);

        $this->mailer->send($email);
    }

    private function buildContext(ExtranetUser $extranetUser, array $context = []): array
    {
        return $context + [
            'extranetUser' => $this->normalizer->normalize($extranetUser, null, [
                'groups' => [
                    'extranet_password_email',
                ],
            ]),
        ];
    }
}
