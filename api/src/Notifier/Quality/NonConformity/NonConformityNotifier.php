<?php

declare(strict_types=1);

namespace App\Notifier\Quality\NonConformity;

use App\Entity\Directory\People;
use App\Entity\Quality\NonConformity;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NonConformityNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
    ) {
    }

    public function sendCreation(NonConformity $nonConformity): void
    {
        if ([] === ($recipients = $this->recipientsFinder->findTos($nonConformity))) {
            return;
        }
        $productTypes = [];
        foreach ($nonConformity->getProducts() as $product) {
            if (\in_array($productType = $product->getFamily()->getProductType()->getEnglishName(), $productTypes, true)) {
                continue;
            }
            $productTypes[] = $productType;
        }

        if ($nonConformity->getProducts()->isEmpty() && !$nonConformity->environmentalIssue) {
            $productTypes[] = NonConformity::OTHER_MODEL;
        }

        if ($nonConformity->environmentalIssue) {
            $productTypes[] = NonConformity::ENVIRONMENTAL_ISSUE;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findCcs($nonConformity)))
            ->subject($nonConformity->rush ? 'ncr.creation.subject_rush' : 'ncr.creation.subject')
            ->htmlTemplate('Emails/Quality/NonConformity/creation.html.twig')
            ->context($this->buildContext($nonConformity, ['product_types' => implode(', ', $productTypes)]));

        $this->mailer->send($email);
    }

    public function sendClosed(NonConformity $nonConformity, array $context): void
    {
        $email = (new TemplatedEmail())
            ->to($nonConformity->reportedBy->getEmail())
            ->subject(NonConformity::CLOSED === $nonConformity->status ? 'ncr.closed.subject_closed' : 'ncr.closed.subject_rejected')
            ->htmlTemplate('Emails/Quality/NonConformity/closed.html.twig')
            ->context($this->buildContext($nonConformity, $context));

        $this->mailer->send($email);
    }

    private function buildContext(NonConformity $nonConformity, array $context = []): array
    {
        return $context + [
            'id' => $nonConformity->getId(),
            'nonConformity' => $this->normalizer->normalize($nonConformity, null, [
                'groups' => ['non_conformity', 'non_conformity:detail', 'people_public', 'currency', 'part'],
            ]),
        ];
    }
}
