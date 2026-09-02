<?php

declare(strict_types=1);

namespace App\Notifier\Sales\Demo;

use App\Entity\Directory\People;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\Demo;
use App\Repository\Directory\PeopleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DemoNotifier
{
    public function __construct(
        private readonly Security $security,
        private readonly NormalizerInterface $normalizer,
        private readonly UrlGeneratorInterface $router,
        private readonly RecipientsFinder $recipientsFinder,
        private readonly MailerInterface $mailer,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    public function sendDelinquentEmail(Demo $demo, string $reason): void
    {
        $context = ['reason' => $reason];

        $recipients = $this->recipientsFinder->findRecipients($demo);

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('demo.subject')
            ->htmlTemplate('Emails/Sales/Demo/demo_delinquent_notification.html.twig')
            ->context($this->buildContext($demo, $context));

        $this->mailer->send($email);
    }

    public function sendCommentEmail(Demo $demo, string $comment): void
    {
        $closedComment = \in_array($demo->getStatus(), Demo::CLOSED_STATUSES, true);
        $context = ['comment' => $comment, 'closedComment' => $closedComment];

        $recipients = [...$this->recipientsFinder->findRecipients($demo), ...$this->peopleRepository->findGroupsMembers(['role_GCTO', 'role_GPID'])];

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('demo.subject')
            ->addCc(...array_map(static fn (People $cc) => $cc->getEmail(), $this->recipientsFinder->findCc($demo)))
            ->htmlTemplate('Emails/Sales/Demo/demo_comment_notification.html.twig')
            ->context($this->buildContext($demo, $context));

        $this->mailer->send($email);
    }

    public function sendStatusEmail(Demo $demo): void
    {
        $recipients = [...$this->recipientsFinder->findRecipients($demo), ...$this->peopleRepository->findGroupsMembers(['role_GCTO', 'role_GPID'])];
        if (Demo::ACTIVE === $demo->getStatus()) {
            $recipients = [...$recipients, ...$this->peopleRepository->findGroupsMembers(['ROLE_LM'])];
        }

        if ($demo->getCustomer()->getCustomerTypes()->filter(static fn (CustomerType $customerType) => CustomerType::MILITARY_TYPE_NAME === $customerType->getName())->count() > 0) {
            $recipients = [...$recipients, ...$this->peopleRepository->findGroupsMembers(['ROLE_VPM'])];
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients))
            ->subject('demo.subject')
            ->addCc(...array_map(static fn (People $cc) => $cc->getEmail(), $this->recipientsFinder->findCc($demo)))
            ->htmlTemplate('Emails/Sales/Demo/demo_status_notification.html.twig')
            ->context($this->buildContext($demo));

        $this->mailer->send($email);
    }

    public function sendReminderEmail(Demo $demo): void
    {
        $email = (new TemplatedEmail())
            ->to($demo->getAsm()->getEmail())
            ->subject('demo.subject_reminder')
            ->htmlTemplate('Emails/Sales/Demo/demo_reminder_notification.html.twig')
            ->context($this->buildContext($demo));

        $this->mailer->send($email);
    }

    private function buildContext(Demo $demo, array $context = []): array
    {
        return $context + [
            'user' => $this->normalizer->normalize($this->security->getUser(), null, ['groups' => ['people_public']]),
            'eCustomer' => $demo->getCustomer()->getName(),
            'model' => $demo->getProduct()->getName(),
            'url' => $this->router->generate('demos', ['id' => $demo->getId()], Router::ABSOLUTE_URL),
            'demo_normalized' => $this->normalizer->normalize($demo, null, [
                'groups' => [
                    'demo',
                    'demo_detail',
                    'people_list',
                    'country_list',
                    'location_public',
                    'product_restricted',
                    'customer_list',
                ],
            ]),
        ];
    }
}
