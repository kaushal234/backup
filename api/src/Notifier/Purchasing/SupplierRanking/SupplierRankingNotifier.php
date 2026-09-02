<?php

declare(strict_types=1);

namespace App\Notifier\Purchasing\SupplierRanking;

use App\Entity\Directory\People;
use App\Entity\Purchasing\Supplier\Supplier;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SupplierRankingNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Security $security,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * Send notification to Buyer when a supplier ranking classification has been decresed.
     *
     * @return void
     */
    public function sendOnDecreasedClassification(SupplierRanking $supplierRanking, SupplierRanking $previousSupplierRanking)
    {
        if (!$supplierRanking->supplier instanceof Supplier) {
            return;
        }

        if (null === $supplierRanking->supplier->location) {
            return;
        }

        if ([] === ($recipients = $this->recipientsFinder->findDecreasedClassificationRecipients($supplierRanking->supplier->location))) {
            return;
        }

        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->addTo($currentUser->getEmail())
            ->subject($this->translator->trans('supplier_ranking.classification_decreased_subject', ['supplierName' => $supplierRanking->getSupplierName()], 'emails'))
            ->htmlTemplate('Emails/Purchasing/SupplierRanking/decreased_classification.html.twig')
            ->context([
                'supplierRanking' => $supplierRanking,
                'oldClassification' => $previousSupplierRanking->classification->name,
                'currentUser' => (string) $currentUser,
                'currentUserId' => $currentUser->getId(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * Send notification to buyer when a review is needed on supplier rankings.
     * Send a list of supplier rankings.
     *
     * @return void
     */
    public function sendReviewNotification(SupplierRanking $supplierRanking)
    {
        if (!$supplierRanking->supplier instanceof Supplier) {
            return;
        }

        if (null === $supplierRanking->supplier->location) {
            return;
        }

        if ([] === ($recipients = $this->recipientsFinder->findReviewRecipients($supplierRanking->supplier->location))) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->cc(...array_map(static fn (string $recipient) => $recipient, $this->recipientsFinder->findReviewCc($supplierRanking->supplier->location)))
            ->subject($this->translator->trans('supplier_ranking.review_subject', ['supplierName' => $supplierRanking->getSupplierName()], 'emails'))
            ->htmlTemplate('Emails/Purchasing/SupplierRanking/review.html.twig')
            ->context([
                'supplierRanking' => $supplierRanking,
            ]);

        if ($supplierRanking->supplier->getMasterBuyer() instanceof People) {
            $email->addTo($supplierRanking->supplier->getMasterBuyer()->getEmail());
        }

        $this->mailer->send($email);
    }
}
