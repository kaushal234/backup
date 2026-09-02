<?php

declare(strict_types=1);

namespace App\Notifier\Legal;

use App\Entity\Directory\People;
use App\Entity\Legal\Contract;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContractExpirationNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly RecipientFinder $recipientFinder,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    /**
     * Send expiration notification for a contract.
     *
     * @throws TransportExceptionInterface
     */
    public function notifyExpiration(Contract $contract): void
    {
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientFinder->findTos($contract)))
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientFinder->findCcs($contract)))
            ->subject('contract.subject.expired')
            ->htmlTemplate('Emails/Legal/contract_expired.html.twig')
            ->context($this->buildContext($contract));

        $this->mailer->send($email);
    }

    /**
     * Send notification for a contract expiring in one month.
     *
     * @throws TransportExceptionInterface
     */
    public function notifyExpiringSoon(Contract $contract): void
    {
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientFinder->findTos($contract)))
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientFinder->findCcs($contract)))
            ->subject('contract.subject.expire_soon')
            ->htmlTemplate('Emails/Legal/contract_expiring_soon.html.twig')
            ->context($this->buildContext($contract));

        $this->mailer->send($email);
    }

    private function buildContext(Contract $contract): array
    {
        return [
            'contract' => $this->normalizer->normalize($contract, null, ['groups' => ['contract', 'contract:item', 'people_public', 'sub_category', 'category', 'currency', 'region_light', 'division', 'premise', 'business_unit', 'address', 'customer_list']]),
        ];
    }
}
