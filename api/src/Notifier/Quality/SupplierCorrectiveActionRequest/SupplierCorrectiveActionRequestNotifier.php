<?php

declare(strict_types=1);

namespace App\Notifier\Quality\SupplierCorrectiveActionRequest;

use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SupplierCorrectiveActionRequestNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function sendStatus(SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest, array $context): void
    {
        if (empty($recipients = $this->recipientsFinder->findTos($supplierCorrectiveActionRequest))) {
            return;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (string $email) => $email, $recipients))
            ->cc(...array_map(static fn (string $email) => $email, $this->recipientsFinder->findCcs($supplierCorrectiveActionRequest)))
            ->subject('scar.subject_status')
            ->htmlTemplate('Emails/Quality/SupplierCorrectiveActionRequest/supplier_corrective_action_request_status.html.twig')
            ->context($this->buildContext($supplierCorrectiveActionRequest, $context));

        $this->mailer->send($email);
    }

    public function sendComment(SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest, UserInterface $poster, Comment $comment, $tos = [], ?\SplFileInfo $attachment = null): void
    {
        $recipients = $poster instanceof User ? [$poster->getEmail()] : $comment->metadata['email'] ?? [];
        $recipients = [...$recipients, ...$this->recipientsFinder->findTos($supplierCorrectiveActionRequest, false)];

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (string $email) => $email, $recipients))
            ->cc(...array_map(static fn (string $email) => $email, $this->recipientsFinder->findCcs($supplierCorrectiveActionRequest)))
            ->subject('scar.subject_comment')
            ->htmlTemplate('Emails/Quality/SupplierCorrectiveActionRequest/supplier_corrective_action_request_comment.html.twig')
            ->context($this->buildContext($supplierCorrectiveActionRequest, ['message' => $comment->getMessage(), 'metadata' => $comment->metadata, 'discriminator' => $comment->discriminator], $poster));

        if (null !== $attachment) {
            $email->attachFromPath($attachment->getRealPath());
        }

        if (!empty($tos)) {
            $email->addTo(...$tos);
        }

        $this->mailer->send($email);

        foreach (array_merge($email->getCc(), $email->getTo()) as $recipient) {
            $comment->metadata['recipients'][] = $recipient->getAddress();
        }

        if (null !== ($comment->metadata['recipients'] ?? null)) {
            $comment->metadata['recipients'] = array_unique($comment->metadata['recipients']);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
        }
    }

    private function buildContext(SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest, array $context = [], ?UserInterface $poster = null): array
    {
        return $context + [
            'id' => $supplierCorrectiveActionRequest->getId(),
            'current_status' => $supplierCorrectiveActionRequest->getStatus(),
            'supplierCorrectiveActionRequest' => $this->normalizer->normalize($supplierCorrectiveActionRequest, null, [
                'groups' => ['supplier_corrective_action_request', 'supplier_corrective_action_request:detail', 'people_public', 'location_public'],
            ]),
            'poster' => $poster instanceof User ? $this->normalizer->normalize($poster, null, ['groups' => ['people_public']]) : [],
            'posterType' => $poster instanceof People ? 'tld' : 'supplier',
        ];
    }
}
