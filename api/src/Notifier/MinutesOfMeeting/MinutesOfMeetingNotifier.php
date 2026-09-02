<?php

declare(strict_types=1);

namespace App\Notifier\MinutesOfMeeting;

use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\MinutesOfMeeting\Meeting;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MinutesOfMeetingNotifier
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly MailerInterface $mailer,
        private readonly RecipientsFinder $recipientsFinder,
    ) {
    }

    public function sendCreationEmail(Meeting $meeting): void
    {
        $email = (new TemplatedEmail())
            ->to($meeting->getCreatedBy()->getEmail())
            ->subject('mom.creation_subject')
            ->htmlTemplate('Emails/MinutesOfMeeting/meeting_creation.html.twig')
            ->context($this->buildContext($meeting, ['createdBy' => (string) $meeting->getCreatedBy(), 'title' => $meeting->getTitle()]));

        $this->mailer->send($email);
    }

    public function sendStatusEmail(Meeting $meeting): void
    {
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findRecipients($meeting)))
            ->addCc(...array_map(static fn (People $cc) => $cc->getEmail(), $this->recipientsFinder->findCc($meeting)))
            ->subject('mom.status_subject')
            ->htmlTemplate('Emails/MinutesOfMeeting/meeting_status_update.html.twig')
            ->context($this->buildContext($meeting, ['status' => $meeting->getStatus(), 'title' => $meeting->getTitle()]));

        $this->mailer->send($email);
    }

    public function sendReleasedSubscriptionEmail(Meeting $meeting, User $subscriber): void
    {
        $email = (new TemplatedEmail())
            ->to($subscriber->getEmail())
            ->subject('mom.subscription_subject')
            ->htmlTemplate('Emails/MinutesOfMeeting/meeting_released_subscription.html.twig')
            ->context($this->buildContext($meeting, ['status' => $meeting->getStatus(), 'title' => $meeting->getTitle()]));

        $this->mailer->send($email);
    }

    public function sendCommentEmail(Meeting $meeting, Comment $comment): void
    {
        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $this->recipientsFinder->findRecipients($meeting)))
            ->subject('mom.comment_subject')
            ->htmlTemplate('Emails/MinutesOfMeeting/meeting_comment.html.twig')
            ->context($this->buildContext($meeting, ['comment' => $comment->getMessage(), 'user' => (string) $comment->getUser()]));

        $this->mailer->send($email);
    }

    private function buildContext(Meeting $meeting, array $context = []): array
    {
        /** @var array $normalizedMeeting */
        $normalizedMeeting = $this->normalizer->normalize($meeting, null, [
            'groups' => ['meeting', 'meeting:detail', 'customer_list', 'people_public', 'competitor_public'],
        ]);

        $meetingCreator = $meeting->getCreatedBy();
        $normalizedMeeting['customers'] = $normalizedMeeting['competitors'] = $normalizedMeeting['customerContacts'] = $normalizedMeeting['contacts'] = '';
        $normalizedMeeting['createdBy'] = \sprintf('%s, %s', $meetingCreator->getLastname(), $meetingCreator->getFirstname());

        $customers = [];
        foreach ($meeting->getCustomers() as $customer) {
            $customers[] = $customer->getName();
        }
        $normalizedMeeting['customers'] = implode(', ', $customers);

        $competitors = [];
        foreach ($meeting->getCompetitors() as $competitor) {
            $competitors[] = $competitor->getName();
        }
        $normalizedMeeting['competitors'] = implode(', ', $competitors);

        $customerContacts = [];
        foreach ($meeting->getCustomerContacts() as $customerContact) {
            $customerContacts[] = \sprintf('%s, %s (%s)', $customerContact->getLastname(), $customerContact->getFirstname(), $customerContact->getExtranetUserProfile()->companyName);
        }
        $normalizedMeeting['customerContacts'] = implode(', ', $customerContacts);

        $contacts = [];
        foreach ($meeting->getContacts() as $contact) {
            $contacts[] = \sprintf('%s, %s (%s)', $contact->getFirstName(), $contact->getLastName(), $contact->getCompany());
        }
        $normalizedMeeting['contacts'] = implode(', ', $contacts);

        $businessUnits = [];
        foreach ($meeting->getBusinessUnits() as $businessUnit) {
            $businessUnits[] = \sprintf('%s', $businessUnit->getName());
        }
        $normalizedMeeting['businessUnits'] = implode(', ', $businessUnits);

        $attendees = [];
        foreach ($meeting->getAttendees() as $attendee) {
            $attendees[] = \sprintf('%s %s', $attendee->getFirstName(), $attendee->getLastName());
        }
        $normalizedMeeting['attendees'] = implode(', ', $attendees);

        return $context + [
            'meeting_normalized' => $normalizedMeeting,
            'id' => (string) $meeting->getId(),
        ];
    }
}
