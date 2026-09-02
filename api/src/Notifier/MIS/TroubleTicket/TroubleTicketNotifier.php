<?php

declare(strict_types=1);

namespace App\Notifier\MIS\TroubleTicket;

use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TroubleTicketNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function sendNotification(TroubleTicket $troubleTicket, string $subject, ?People $user = null, array $changeSet = [], bool $notifyOperationals = true): void
    {
        $recipients = $this->recipientsFinder->findTos($troubleTicket, $notifyOperationals);
        // remove current user from TTS notification
        if (($key = array_search($user, $recipients, true)) !== false) {
            unset($recipients[$key]);
        }

        if (!empty($css = $this->recipientsFinder->findCcs($troubleTicket)) || !empty($recipients)) {
            $email = (new TemplatedEmail())
                ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
                ->cc(...array_map(static fn (People $cc) => $cc->getEmail(), $css))
                ->subject(\sprintf('trouble_ticket.subject.%s', $subject))
                ->htmlTemplate(\sprintf('Emails/MIS/TroubleTicket/%s.html.twig', $subject))
                ->context($this->buildContext($troubleTicket, [...$changeSet, 'user_fullname' => $user?->getDisplayName()]));

            $this->mailer->send($email);
        }
    }

    public function sendJiraReminder(array $troubleTickets, string $to): void
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('trouble_ticket.subject.jira_reminder')
            ->htmlTemplate('Emails/MIS/TroubleTicket/jira_reminder.html.twig')
            ->context(['troubleTickets' => $this->normalizer->normalize($troubleTickets, TroubleTicket::class, ['groups' => ['trouble_ticket:reminder', 'module:list', 'people_public']])]);

        $this->mailer->send($email);
    }

    private function buildContext(TroubleTicket $troubleTicket, array $context = []): array
    {
        if (isset($context['changeSet'])) {
            $changeSet = $context['changeSet'];
            foreach ($changeSet as $key => $value) {
                $translationPath = 'trouble_ticket.change_set.'.$key;
                if ($translationPath !== $newKey = $this->translator->trans($translationPath, [], 'emails')) {
                    $changeSet[$newKey] = $value;
                    unset($changeSet[$key]);
                }
            }
            $context['changeSet'] = $changeSet;
        }

        return [
            ...$context,
            'comment' => $troubleTicket->comment,
            'id' => $troubleTicket->getId(),
            'module' => $troubleTicket->module->getName(),
            'application' => $troubleTicket->module->getApplication()->name,
            'business_unit' => $troubleTicket->createdBy->getBusinessUnit()->getLocation()->getName(),
            'assignee_fullname' => $troubleTicket->assignee?->getDisplayName() ?? 'MIS',
            'troubleTicket' => $this->normalizer->normalize($troubleTicket, null, ['groups' => TroubleTicket::ITEM_NORMALIZATION_GROUPS]),
        ];
    }
}
