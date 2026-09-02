<?php

declare(strict_types=1);

namespace App\Notifier\Purchasing;

use App\ION\Resources\Procurement\Orders\Statistic\Contact;
use App\ION\Resources\Procurement\Orders\Statistic\PurchaseOrderStatistic;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VendorUserNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function sendWeeklyReminder(PurchaseOrderStatistic $orderStatistic, array $context): void
    {
        $email = (new TemplatedEmail())
            ->to(...$this->validateEmail($this->filterEmails($orderStatistic->getContacts()), $orderStatistic))
            ->cc(...$this->filterEmails($orderStatistic->getInternalContacts()))
            ->subject('vendor.weekly_reminder.subject')
            ->htmlTemplate('Emails/Purchasing/vendor_weekly_reminder.html.twig')
            ->context($context + $this->buildContext($orderStatistic));

        if (empty($email->getTo())) {
            return;
        }

        $this->mailer->send($email);
    }

    private function buildContext(PurchaseOrderStatistic $orderStatistic): array
    {
        return [
            'orderStatistic' => $this->normalizer->normalize($orderStatistic, null, ['groups' => ['purchase_order_statistic']]),
            'supplierName' => $orderStatistic->buyFromSupplierName,
        ];
    }

    /**
     * @param array|Contact[] $contacts
     *
     * @return array|string[]
     */
    private function filterEmails(array $contacts): array
    {
        return array_map(
            static fn (Contact $recipient) => $recipient->emailAddress,
            array_filter(
                $contacts,
                static fn (Contact $recipient) => '' !== $recipient->emailAddress
            )
        );
    }

    private function validateEmail(array $emails, PurchaseOrderStatistic $orderStatistic): array
    {
        foreach ($emails as $key => $email) {
            $constraints = $this->validator->validate($email, new Email());
            if (0 === $constraints->count()) {
                continue;
            }

            unset($emails[$key]);

            $notValidContact = null;
            foreach ($orderStatistic->getContacts() as $contact) {
                if ($contact->emailAddress !== $email) {
                    continue;
                }

                $notValidContact = $contact;
            }

            if (null === $notValidContact) {
                continue;
            }

            $warningEmail = (new TemplatedEmail())
                ->to(...$this->filterEmails($orderStatistic->getInternalContacts()))
                ->subject('vendor.email_not_valid.subject')
                ->htmlTemplate('Emails/Purchasing/email_not_valid.html.twig')
                ->context(['contact' => $notValidContact->code]);

            if (empty($warningEmail->getTo())) {
                continue;
            }

            $this->mailer->send($warningEmail);
        }

        return $emails;
    }
}
