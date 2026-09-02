<?php

declare(strict_types=1);

namespace App\Command\News;

use App\Entity\Directory\People;
use App\Entity\HumanResources\Event;
use App\Entity\HumanResources\Job;
use App\Entity\News\News;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsCommand(name: 'api:newsletter', description: 'Send weekly newsletter')]
class NewsletterCommand extends Command
{
    private const MAX_RECIPIENTS_PER_EMAIL = 50;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly NormalizerInterface $normalizer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $recipients = $this->entityManager->getRepository(People::class)->findGroupMembers('ACL_AUTH_INTRANET');

        $groupedRecipients = [];

        foreach ($recipients as $recipient) {
            $locationName = $recipient->getBusinessUnit()?->getLocation()?->getName();

            if (!$locationName || null === $recipient->getEmail()) {
                continue;
            }

            $groupedRecipients[$locationName][] = $recipient->getEmail();
        }

        if (empty($groupedRecipients)) {
            $output->writeln('No email found');

            return Command::SUCCESS;
        }

        $today = new \DateTime();
        $startOfWeek = (clone $today)->modify('this Monday');
        $endOfWeek = (clone $today)->modify('this Sunday');
        $oneMonthAgo = new \DateTimeImmutable('-1 month');

        $lastNews = $this->entityManager->getRepository(News::class)->findLatestNewsByCategoryFilter('talent', true, 3);
        $lastTalentNews = $this->entityManager->getRepository(News::class)->findLatestNewsByCategoryFilter('talent', false, 3);
        $lastJobs = $this->entityManager->getRepository(Job::class)->findLatestJobs(5);
        $events = $this->entityManager->getRepository(Event::class)->findEventsThisWeek($startOfWeek, $endOfWeek);
        $newPeople = $this->entityManager->getRepository(People::class)->findNewPeopleSince($oneMonthAgo, 5);

        foreach ($groupedRecipients as $location => $emails) {
            $chunks = array_chunk($emails, self::MAX_RECIPIENTS_PER_EMAIL);

            foreach ($chunks as $index => $emailChunk) {
                $email = (new TemplatedEmail())
                    ->cc(...$emailChunk)
                    ->subject('ALVEST Weekly Newsletter')
                    ->htmlTemplate('Emails/News/newsletter.html.twig')
                    ->context([
                        'lastNews' => $lastNews,
                        'lastJobs' => $lastJobs,
                        'events' => $events,
                        'lastTalentNews' => $lastTalentNews,
                        'newPeople' => $this->normalizer->normalize($newPeople, null, ['groups' => ['people_detail']]),
                    ])
                ;

                $this->mailer->send($email);

                $output->writeln(\sprintf(
                    'Newsletter sent to %d people in %s (batch %d/%d).',
                    \count($emailChunk),
                    $location,
                    $index + 1,
                    \count($chunks)
                ));
            }
        }

        return Command::SUCCESS;
    }
}
