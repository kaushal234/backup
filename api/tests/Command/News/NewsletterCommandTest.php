<?php

declare(strict_types=1);

namespace App\Tests\Command\News;

use App\Command\News\NewsletterCommand;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\HumanResources\Event;
use App\Entity\HumanResources\Job;
use App\Entity\News\News;
use App\Repository\Directory\PeopleRepository;
use App\Repository\HumanResources\EventsRepository;
use App\Repository\HumanResources\JobRepository;
use App\Repository\News\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NewsletterCommandTest extends TestCase
{
    public function testNewsletterCommandCallsRepositoriesAndSendsExpectedEmail(): void
    {
        $news = [new News()];
        $talentNews = [new News()];
        $jobs = [new Job()];
        $events = [new Event()];
        $people = [new People()];

        // Repositories mock
        $newsRepo = $this->createMock(NewsRepository::class);
        $newsRepo->expects($this->exactly(2))
            ->method('findLatestNewsByCategoryFilter')
            ->willReturnCallback(static function (string $category, bool $exclude, ?int $limit) use ($news, $talentNews) {
                if ('talent' === $category && true === $exclude && 3 === $limit) {
                    return $news;
                }
                if ('talent' === $category && false === $exclude && 3 === $limit) {
                    return $talentNews;
                }
                throw new \InvalidArgumentException('Unexpected arguments for findLatestNewsByCategoryFilter');
            });

        $jobRepo = $this->createMock(JobRepository::class);
        $jobRepo->expects($this->once())
            ->method('findLatestJobs')
            ->with(5)
            ->willReturn($jobs);

        $eventRepo = $this->createMock(EventsRepository::class);
        $eventRepo->expects($this->once())
            ->method('findEventsThisWeek')
            ->with($this->isInstanceOf(\DateTimeInterface::class), $this->isInstanceOf(\DateTimeInterface::class))
            ->willReturn($events);

        $peopleRepo = $this->createMock(PeopleRepository::class);
        $peopleRepo->expects($this->once())
            ->method('findNewPeopleSince')
            ->with($this->isInstanceOf(\DateTimeImmutable::class), 5)
            ->willReturn($people);

        $mockLocation = $this->createMock(Location::class);
        $mockLocation->method('getName')->willReturn('Paris');

        $mockBusinessUnit = $this->createMock(BusinessUnit::class);
        $mockBusinessUnit->method('getLocation')->willReturn($mockLocation);

        $mockPersons = [];
        for ($i = 1; $i <= 60; ++$i) {
            $mockPerson = $this->createMock(People::class);
            $mockPerson->method('getEmail')->willReturn(\sprintf('user%d@example.com', $i));
            $mockPerson->method('getBusinessUnit')->willReturn($mockBusinessUnit);
            $mockPersons[] = $mockPerson;
        }

        $peopleRepo->expects($this->once())
            ->method('findGroupMembers')
            ->with('ACL_AUTH_INTRANET')
            ->willReturn($mockPersons);

        // EntityManager
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnCallback(static function (string $class) use (
            $newsRepo, $jobRepo, $eventRepo, $peopleRepo
        ) {
            return match ($class) {
                News::class => $newsRepo,
                Job::class => $jobRepo,
                Event::class => $eventRepo,
                People::class => $peopleRepo,
                default => throw new \InvalidArgumentException("Unexpected class: $class"),
            };
        });

        // Normalizer
        $normalizer = $this->createMock(NormalizerInterface::class);
        $normalizer->expects($this->atLeastOnce())
            ->method('normalize')
            ->with($people, null, ['groups' => ['people_detail']])
            ->willReturn(['normalized_people']);

        // Mailer
        $mailer = $this->createMock(MailerInterface::class);

        $mailer->expects($this->exactly(2))
            ->method('send')
            ->with($this->callback(function (TemplatedEmail $email) use (
                $news, $jobs, $events, $talentNews
            ) {
                $this->assertSame('Emails/News/newsletter.html.twig', $email->getHtmlTemplate());

                $context = $email->getContext();
                $this->assertSame($news, $context['lastNews']);
                $this->assertSame($jobs, $context['lastJobs']);
                $this->assertSame($events, $context['events']);
                $this->assertSame($talentNews, $context['lastTalentNews']);
                $this->assertSame(['normalized_people'], $context['newPeople']);

                $this->assertLessThanOrEqual(50, \count($email->getCc()));

                return true;
            }));

        $command = new NewsletterCommand(
            $em,
            $mailer,
            $normalizer
        );

        $tester = new CommandTester($command);
        $exitCode = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }
}
