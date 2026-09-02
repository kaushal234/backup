<?php

declare(strict_types=1);

namespace App\Notifier\Purchasing\VendorWarrantyClaim;

use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Purchasing\VendorWarrantyClaim;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VendorWarrantyClaimNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly Security $security,
    ) {
    }

    public function sendCreation(VendorWarrantyClaim $vendorWarrantyClaim): void
    {
        if ([] === ($recipients = $this->recipientsFinder->findCreationTos($vendorWarrantyClaim))) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('vwc.creation.subject')
            ->htmlTemplate('Emails/Purchasing/VendorWarrantyClaim/creation_status.html.twig')
            ->context($this->buildContext($vendorWarrantyClaim, null, ['action' => 'creation']));

        $this->mailer->send($email);
    }

    public function sendAssignee(VendorWarrantyClaim $vendorWarrantyClaim, ?People $previousAssignee = null): void
    {
        if (null === $vendorWarrantyClaim->assignee) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to($vendorWarrantyClaim->assignee->getEmail())
            ->subject('vwc.assignee.subject')
            ->htmlTemplate('Emails/Purchasing/VendorWarrantyClaim/new_assignee.html.twig')
            ->context($this->buildContext($vendorWarrantyClaim));

        if (null !== $previousAssignee) {
            $email->cc($previousAssignee->getEmail());
        }

        $this->mailer->send($email);
    }

    public function sendStatus(VendorWarrantyClaim $vendorWarrantyClaim, string $previousStatus): void
    {
        if ([] === ($recipients = $this->recipientsFinder->findStatusRecipients($vendorWarrantyClaim))) {
            return;
        }
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (string $recipient) => $recipient, $recipients))
            ->cc(...array_map(static fn (string $recipient) => $recipient, $this->recipientsFinder->findStatusCcs($vendorWarrantyClaim)))
            ->subject('vwc.status.subject')
            ->htmlTemplate('Emails/Purchasing/VendorWarrantyClaim/creation_status.html.twig')
            ->context($this->buildContext($vendorWarrantyClaim, null, ['action' => 'status', 'previousStatus' => $previousStatus, 'status' => $vendorWarrantyClaim->status->name]));

        $this->mailer->send($email);
    }

    public function sendCommentFromAlvest(VendorWarrantyClaim $vendorWarrantyClaim, Comment $comment, array $recipients): void
    {
        $tos = ($public = $comment->isPublic()) ? $recipients : $this->recipientsFinder->findCommentRecipients($vendorWarrantyClaim);
        $ccs = $public ? $this->recipientsFinder->findCommentRecipients($vendorWarrantyClaim) : $this->recipientsFinder->findInternalNoteCcs($vendorWarrantyClaim);

        if ([] === $tos) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (string $recipient) => $recipient, $tos))
            ->cc(...array_map(static fn (string $recipient) => $recipient, [...$ccs, $comment->getUser()->getEmail()]))
            ->subject($public ? 'vwc.comment.subject' : 'vwc.internal_comment.subject')
            ->htmlTemplate('Emails/Purchasing/VendorWarrantyClaim/comment.html.twig')
            ->context($this->buildContext($vendorWarrantyClaim, $comment));

        $this->mailer->send($email);
    }

    public function sendCommentFromExternal(VendorWarrantyClaim $vendorWarrantyClaim, Comment $comment): void
    {
        if (null === ($to = $vendorWarrantyClaim->assignee)) {
            return;
        }

        $poster = ($user = $this->security->getUser()) instanceof VendorUser ? $user->getEmail() : $comment->metadata['email'];

        $email = (new TemplatedEmail())
            ->to($to->getEmail())
            ->cc(...array_map(static fn (string $recipient) => $recipient, array_merge($this->recipientsFinder->findStatusCcs($vendorWarrantyClaim), $this->recipientsFinder->findCommentCcs($vendorWarrantyClaim))))
            ->replyTo($poster)
            ->subject('vwc.external_comment.subject')
            ->htmlTemplate('Emails/Purchasing/VendorWarrantyClaim/external_comment.html.twig')
            ->context($this->buildContext($vendorWarrantyClaim, $comment, ['poster' => $poster]));

        $this->mailer->send($email);
    }

    public function sendVendor(VendorWarrantyClaim $vendorWarrantyClaim): void
    {
        $recipients = $this->recipientsFinder->findVendorRecipients($vendorWarrantyClaim);
        $email = (new TemplatedEmail());
        if (null !== $vendorWarrantyClaim->assignee) {
            $recipients = [...$recipients, $vendorWarrantyClaim->assignee->getEmail()];
            $email->replyTo($vendorWarrantyClaim->assignee->getEmail());
        }

        if ([] === $recipients) {
            return;
        }

        $email
            ->to(...array_map(static fn (string $recipient) => $recipient, $recipients))
            ->subject('vwc.vendor.subject')
            ->htmlTemplate('Emails/Purchasing/VendorWarrantyClaim/vendor.html.twig')
            ->context($this->buildContext($vendorWarrantyClaim));

        $this->mailer->send($email);
    }

    public function sendVendorToResponseReminder(VendorWarrantyClaim $vendorWarrantyClaim): void
    {
        $recipients = $this->recipientsFinder->findVendorRecipients($vendorWarrantyClaim);
        $email = (new TemplatedEmail());
        if (null !== $vendorWarrantyClaim->assignee) {
            $recipients = [...$recipients, $vendorWarrantyClaim->assignee->getEmail()];
            $email->replyTo($vendorWarrantyClaim->assignee->getEmail());
        }

        if ([] === $recipients) {
            return;
        }

        $email
            ->to(...array_map(static fn (string $recipient) => $recipient, $recipients))
            ->subject('vwcvendor_remindersubject')
            ->htmlTemplate('Emails/Purchasing/VendorWarrantyClaim/vendorReminder.html.twig')
            ->context($this->buildContext($vendorWarrantyClaim))
        ;

        $this->mailer->send($email);
    }

    private function buildContext(VendorWarrantyClaim $vendorWarrantyClaim, ?Comment $comment = null, array $context = []): array
    {
        return $context + [
            'id' => $vendorWarrantyClaim->getId(),
            'vendorWarrantyClaim' => $this->normalizer->normalize($vendorWarrantyClaim, null, [
                'groups' => VendorWarrantyClaim::ITEM_NORMALIZATION_GROUPS,
            ]),
            'comment' => null !== $comment ? $this->normalizer->normalize($comment, null, ['groups' => ['activity']]) : [],
        ];
    }
}
