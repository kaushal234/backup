<?php

declare(strict_types=1);

namespace App\Notifier\Quality\FirstArticleQualification;

use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FirstArticleQualificationNotifier
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
        private readonly RecipientsFinder $recipientsFinder,
    ) {
    }

    public function sendCreation(FirstArticleQualification $firstArticleQualification): void
    {
        $context = [
            'partNumbers_normalized' => $this->normalizer->normalize($firstArticleQualification->getPartNumbers(), null, [
                'groups' => [
                    'faq_detail', 'faq_plan_item_detail', 'faq_part_number', 'faq_plan_item', 'people_public', 'file', 'expose_legacy', 'location_public', 'tag', 'faq_item_type_detail', 'equipment_record',
                ],
            ]),
        ];

        $email = (new TemplatedEmail())
            ->addTo($firstArticleQualification->getOwner()->getEmail())
            ->addCc(...$this->recipientsFinder->findCCs($firstArticleQualification))
            ->subject('first_article_qualification.subjects.creation')
            ->htmlTemplate('Emails/Quality/FirstArticleQualification/first_article_qualification_creation.html.twig')
            ->context($this->buildContext($firstArticleQualification, $context));

        $this->mailer->send($email);
    }

    public function sendComment(FirstArticleQualification $firstArticleQualification, Comment $comment): void
    {
        $email = (new TemplatedEmail())
            ->addTo($firstArticleQualification->getOwner()->getEmail())
            ->addCc(...$this->recipientsFinder->findCCs($firstArticleQualification))
            ->subject('first_article_qualification.subjects.comment')
            ->htmlTemplate('Emails/Quality/FirstArticleQualification/first_article_qualification_comment.html.twig')
            ->context($this->buildContext($firstArticleQualification, ['comment' => $comment]));

        $this->mailer->send($email);
    }

    public function sendFileAdded(FirstArticleQualification $firstArticleQualification, User $poster): void
    {
        $email = (new TemplatedEmail())
            ->addTo($firstArticleQualification->getOwner()->getEmail())
            ->addCc(...$this->recipientsFinder->findCCs($firstArticleQualification))
            ->subject('first_article_qualification.subjects.file_added')
            ->htmlTemplate('Emails/Quality/FirstArticleQualification/first_article_qualification_file_added.html.twig')
            ->context($this->buildContext($firstArticleQualification, ['posterLastname' => $poster->getLastname(), 'posterFirstname' => $poster->getFirstname()]));

        $this->mailer->send($email);
    }

    public function sendStatus(FirstArticleQualification $firstArticleQualification, ?string $previousStatus = null, ?User $actor = null): void
    {
        $email = (new TemplatedEmail())
            ->addTo(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findStatusRecipients($firstArticleQualification)))
            ->addCc(...$this->recipientsFinder->findCCs($firstArticleQualification))
            ->subject('first_article_qualification.subjects.status_change')
            ->htmlTemplate('Emails/Quality/FirstArticleQualification/first_article_qualification_status_notification.html.twig')
            ->context($this->buildContext($firstArticleQualification, [
                'previousStatus' => $previousStatus,
                'newStatus' => $firstArticleQualification->getStatus(),
                'actorName' => null !== $actor ? $actor->getLastname().' '.$actor->getFirstname() : null,
                'changedAt' => new \DateTime(),
            ]));
        $this->mailer->send($email);
    }

    public function sendReminderStatus(array $firstArticleQualifications): void
    {
        foreach ($firstArticleQualifications as $reminderStatus => $faqs) {
            $faqNormalized = $this->normalizer->normalize($faqs, null, ['groups' => ['faq', 'people_public']]);

            $email = (new TemplatedEmail())
                ->addTo(...$this->recipientsFinder->findReminderStatusRecipients($faqs, $reminderStatus))
                ->subject('first_article_qualification.titles.'.mb_strtolower($reminderStatus))
                ->htmlTemplate('Emails/Quality/FirstArticleQualification/first_article_qualification_date_reminder.html.twig')
                ->context(['reminderStatus' => $reminderStatus, 'firstArticleQualifications' => $faqNormalized]);

            $this->mailer->send($email);
        }
    }

    public function sendPlanCompleted(FirstArticleQualification $firstArticleQualification): void
    {
        if (empty($recipients = $this->recipientsFinder->findPlanCompletedRecipients($firstArticleQualification))) {
            return;
        }

        $email = (new TemplatedEmail())
            ->addTo(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('first_article_qualification.subjects.plan_completed')
            ->htmlTemplate('Emails/Quality/FirstArticleQualification/first_article_qualification_plan_completed.html.twig')
            ->context($this->buildContext($firstArticleQualification));

        $this->mailer->send($email);
    }

    public function sendPlanStatus(FirstArticleQualification $firstArticleQualification): void
    {
        $email = (new TemplatedEmail())
            ->to($firstArticleQualification->getPoster()->getEmail())
            ->subject('first_article_qualification.subjects.plan_status')
            ->htmlTemplate('Emails/Quality/FirstArticleQualification/first_article_qualification_plan_status.html.twig')
            ->context($this->buildContext($firstArticleQualification));

        $this->mailer->send($email);
    }

    private function buildContext(FirstArticleQualification $firstArticleQualification, array $context = []): array
    {
        if (isset($context['comment'])) {
            $context['comment'] = $this->normalizer->normalize($context['comment'], null, ['groups' => ['activity', 'people_public']]);
        }

        return $context + [
            'faq_id' => (string) $firstArticleQualification->getId(),
            'status' => $firstArticleQualification->getPlanApprovalStatus(),
            'firstArticleQualification_normalized' => $this->normalizer->normalize($firstArticleQualification, null, [
                'groups' => [
                    'faq_detail', 'faq_plan_item_detail', 'faq_part_number', 'faq_plan_item', 'people_public', 'file', 'expose_legacy', 'location_public', 'tag', 'faq_item_type_detail', 'equipment_record',
                ],
            ]),
        ];
    }
}
