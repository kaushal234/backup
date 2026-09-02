<?php

declare(strict_types=1);

namespace App\Notifier\Quality\Crab;

use App\Entity\Directory\People;
use App\Entity\Quality\Crab;
use App\Entity\Quality\Derogation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CrabNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
    ) {
    }

    public function sendWrite(Crab $crab, People $people, bool $yellowTag): void
    {
        $recipients = $yellowTag ? [$people, ...$this->recipientsFinder->findYellowTagTos($crab)] : [...$this->recipientsFinder->findTos($crab)];

        $subject = $yellowTag ? 'crab.subject' : 'crab.subject_yellow_tag';
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject($subject)
            ->htmlTemplate('Emails/Quality/Crab/write.html.twig')
            ->context($this->buildContext($crab, ['yellowTag' => $yellowTag]));

        if ($yellowTag && null !== ($asmEmail = $crab->equipmentRecord?->getOrder()?->getAsm()->getEmail() ?? null)) {
            $email->cc($asmEmail);
        }

        $this->mailer->send($email);
    }

    public function sendDerogation(Derogation $derogation, string $action): void
    {
        $email = (new TemplatedEmail())
            ->to($derogation->assignee->getEmail())
            ->cc(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findDerogationCcs($derogation)))
            ->subject(\sprintf('crab.derogation.subject_%s', $action))
            ->htmlTemplate(\sprintf('Emails/Quality/Crab/%s_derogation.html.twig', $action))
            ->context($this->buildContext($derogation->getCrabs()->first(), ['assignee' => $derogation->assignee->getDisplayName()]));

        $this->mailer->send($email);
    }

    public function sendDerogationClosed(Derogation $derogation, string $status): void
    {
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findDerogationClosedTos($derogation)))
            ->subject(\sprintf('crab.derogation.subject_%s', $status))
            ->htmlTemplate('Emails/Quality/Crab/closed_derogation.html.twig')
            ->context($this->buildContext($derogation->getCrabs()->first(), ['status' => $status]));

        $this->mailer->send($email);
    }

    public function sendDerogationExpired(Derogation $derogation): void
    {
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findDerogationExpiredTos($derogation)))
            ->subject('crab.derogation.subject_expired')
            ->htmlTemplate('Emails/Quality/Crab/expired_derogation.html.twig')
            ->context($this->buildContext($derogation->getCrabs()->first()));

        $this->mailer->send($email);
    }

    private function buildContext(Crab $crab, array $context = []): array
    {
        return $context + [
            'id' => $crab->getId(),
            'crab' => $this->normalizer->normalize($crab, null, [
                'groups' => ['crab', 'crab:detail', 'location_public', 'equipment_record', 'crab_code', 'people_public', 'non_conformity', 'part', 'expose_legacy', 'product_list', 'derogation', 'derogation:comment', 'crab_departement'],
            ]),
            'model' => $crab->equipmentRecord->getModel(),
        ];
    }
}
