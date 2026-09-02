<?php

declare(strict_types=1);

namespace App\Notifier\Service\CustomerServiceRecord;

use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerServiceRecordCommissioningNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function sendCommissionedImperfectMail(CommissioningCustomerServiceRecord $commissioningCustomerServiceRecord): void
    {
        $subject = $this->translator->trans('csr.commissioned', [
            '%csr%' => $commissioningCustomerServiceRecord->getId(),
            '%rating_result%' => $commissioningCustomerServiceRecord->getRatingResult(),
            '%serial%' => $commissioningCustomerServiceRecord->equipmentRecord->getSerialNumber(),
            '%customer_buyer%' => $commissioningCustomerServiceRecord->equipmentRecord->getBuyer(),
            '%model%' => $commissioningCustomerServiceRecord->equipmentRecord->getModel(),
        ], 'emails');

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findTos($commissioningCustomerServiceRecord)))
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findCcs($commissioningCustomerServiceRecord)))
            ->subject($subject)
            ->htmlTemplate('Emails/Service/CustomerServiceRecord/csr_commissioned.html.twig')
            ->context($this->buildContext($commissioningCustomerServiceRecord));

        $this->mailer->send($email);
    }

    public function sendCommentOnImperfectSurveyMail(CommissioningCustomerServiceRecord $commissioningCustomerServiceRecord, Comment $comment): void
    {
        $author = $comment->getUser();

        // Subjects for CSR emails are always in English, so it is hardcoded here on purpose.
        $subject = \sprintf(
            'CSR#%s - New comment on imperfect survey (%s) - ER#%s %s %s commissioned',
            $commissioningCustomerServiceRecord->getId(),
            $commissioningCustomerServiceRecord->getRatingResult(),
            $commissioningCustomerServiceRecord->equipmentRecord->getSerialNumber(),
            $commissioningCustomerServiceRecord->equipmentRecord->getModel(),
            $commissioningCustomerServiceRecord->equipmentRecord->getBuyer(),
        );

        // The email is sent on behalf of the comment author; FromAddressFixer moves external domains to reply-to.
        $email = (new TemplatedEmail())
            ->from($author instanceof User ? $author->getEmail() : 'noreply@tld-gse.com')
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findTos($commissioningCustomerServiceRecord)))
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findCcs($commissioningCustomerServiceRecord)))
            ->subject($subject)
            ->htmlTemplate('Emails/Service/CustomerServiceRecord/csr_commented.html.twig')
            ->context($this->buildContext($commissioningCustomerServiceRecord, [
                'comment' => $comment->getMessage(),
                'author' => $author instanceof User ? $author->getFullName() : null,
            ]));

        $this->mailer->send($email);
    }

    private function buildContext(CommissioningCustomerServiceRecord $commissioningCustomerServiceRecord, array $context = []): array
    {
        return $context + [
            'id' => $commissioningCustomerServiceRecord->getId(),
            'commissioningCustomerServiceRecord' => $this->normalizer->normalize($commissioningCustomerServiceRecord, null, [
                'groups' => ['customer_service_record:answer', 'customer_service_record', 'customer_service_record:detail', 'equipment_record', 'location_public', 'people_public', 'expose_legacy', 'file', 'phone', 'hour_meter_transaction:light'],
            ]),
        ];
    }
}
