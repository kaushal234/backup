<?php

declare(strict_types=1);

namespace App\Tests\Command\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Command\Service\TechnicianOnCallLateCustomerUpdatesReportCommand;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Factory\SanitizedEmailListFactory;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TechnicianOnCallLateCustomerUpdatesReportCommandTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private MailerInterface&MockObject $mailer;
    private TranslatorInterface&MockObject $translator;
    private IriConverterInterface&MockObject $iriConverter;
    private PeopleRepository&MockObject $peopleRepository;
    private SanitizedEmailListFactory&MockObject $sanitizedEmailListFactory;
    private TechnicianOnCallLateCustomerUpdatesReportCommand $command;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->iriConverter = $this->createMock(IriConverterInterface::class);
        $this->peopleRepository = $this->createMock(PeopleRepository::class);
        $this->sanitizedEmailListFactory = $this->createMock(SanitizedEmailListFactory::class);

        $this->command = new TechnicianOnCallLateCustomerUpdatesReportCommand(
            $this->entityManager,
            $this->mailer,
            $this->translator,
            $this->iriConverter,
            $this->peopleRepository,
            $this->sanitizedEmailListFactory,
        );
    }

    public function testTocWithoutAnyExternalCommentIsLate(): void
    {
        $toc = $this->createToc(id: 1, createdAt: new \DateTime('-10 days'));

        [$lateTocs, $lastExternalLogAtByTocId, $daysSinceLastExternalLogByTocId] = $this->filterLateTocs([$toc], []);

        $this->assertSame([$toc], $lateTocs);
        $this->assertNull($lastExternalLogAtByTocId[1]);
        $this->assertSame(10, $daysSinceLastExternalLogByTocId[1]);
    }

    public function testTocWithRecentExternalCommentIsNotLate(): void
    {
        $toc = $this->createToc(id: 2, createdAt: new \DateTime('-10 days'));
        $comment = $this->createComment('/service/technician_on_calls/2', new \DateTime('-2 days'));

        [$lateTocs] = $this->filterLateTocs([$toc], [$comment]);

        $this->assertSame([], $lateTocs);
    }

    private function createToc(int $id, \DateTime $createdAt): TechnicianOnCall
    {
        $toc = new TechnicianOnCall();
        $toc->createdAt = $createdAt;

        $idProperty = new \ReflectionProperty(TechnicianOnCall::class, 'id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($toc, $id);

        $iri = \sprintf('/service/technician_on_calls/%d', $id);
        $this->iriConverter->method('getIriFromResource')
            ->with($toc)
            ->willReturn($iri);

        return $toc;
    }

    private function createComment(string $resource, \DateTime $createdAt): Comment&MockObject
    {
        $comment = $this->createMock(Comment::class);
        $comment->method('getResource')->willReturn($resource);
        $comment->method('getCreatedAt')->willReturn($createdAt);

        return $comment;
    }

    /**
     * @param array<TechnicianOnCall> $tocs
     * @param array<Comment>          $comments
     *
     * @return array{0: array<TechnicianOnCall>, 1: array<int, \DateTimeInterface|null>, 2: array<int, int>}
     *
     * @throws \ReflectionException
     */
    private function filterLateTocs(array $tocs, array $comments): array
    {
        $query = $this->createMock(Query::class);
        $query->method('getResult')->willReturn($comments);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $commentRepository = $this->createMock(EntityRepository::class);
        $commentRepository->method('createQueryBuilder')->willReturn($queryBuilder);

        $this->entityManager->method('getRepository')
            ->with(Comment::class)
            ->willReturn($commentRepository);

        $method = new \ReflectionMethod(TechnicianOnCallLateCustomerUpdatesReportCommand::class, 'filterLateTocs');
        $method->setAccessible(true);

        return $method->invoke($this->command, $tocs);
    }
}
