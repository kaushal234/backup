<?php

declare(strict_types=1);

namespace App\Tests\AI\Generator;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\Service\ClosureSuggestionOutput;
use App\AI\Factory\AILogFactory;
use App\AI\Platform\Invoker\PlatformInvokerInterface;
use App\AI\Platform\PlatformResult;
use App\AI\Platform\TextPlatform;
use App\AI\Prompt\PromptInterface;
use App\AI\Service\AiJsonResponseParser;
use App\AI\Service\Generator\ClosureSuggestionGenerator;
use App\Entity\Activity\Comment;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ClosureSuggestionGeneratorTest extends TestCase
{
    public function testGenerateThrowsNotFoundWhenTocDoesNotExist(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);

        $em->method('getRepository')->with(TechnicianOnCall::class)->willReturn($repository);
        $repository->method('find')->with(99)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->makeGenerator($em)->generate(99);
    }

    public function testGenerateReturnsClosureSuggestionOutput(): void
    {
        $toc = $this->makeToc();
        $comments = [$this->makeComment('Battery was dead.')];

        $tocRepository = $this->createMock(EntityRepository::class);
        $tocRepository->method('find')->with(1)->willReturn($toc);

        $commentRepository = $this->createMock(EntityRepository::class);
        $commentRepository->method('findBy')->willReturn($comments);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnMap([
            [TechnicianOnCall::class, $tocRepository],
            [Comment::class, $commentRepository],
        ]);

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getIriFromResource')->with($toc)->willReturn('/service/technician_on_calls/1');

        $platformResult = new PlatformResult(
            result: '{"symptoms":"Battery dead","rootCause":"Faulty connection","solution":"Replaced battery"}',
            logIri: '/ai_logs/1',
        );

        $platform = $this->makeFakePlatform($platformResult);
        $parser = new AiJsonResponseParser();

        $result = $this->makeGenerator($em, $platform, $parser, $iriConverter)->generate(1);

        self::assertInstanceOf(ClosureSuggestionOutput::class, $result);
        self::assertSame('Battery dead', $result->symptoms);
        self::assertSame('Faulty connection', $result->rootCause);
        self::assertSame('Replaced battery', $result->solution);
    }

    public function testGenerateWithNoCommentsStillReturnsOutput(): void
    {
        $toc = $this->makeToc();

        $tocRepository = $this->createMock(EntityRepository::class);
        $tocRepository->method('find')->with(1)->willReturn($toc);

        $commentRepository = $this->createMock(EntityRepository::class);
        $commentRepository->method('findBy')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturnMap([
            [TechnicianOnCall::class, $tocRepository],
            [Comment::class, $commentRepository],
        ]);

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getIriFromResource')->willReturn('/service/technician_on_calls/1');

        $platformResult = new PlatformResult(
            result: '{"symptoms":null,"rootCause":null,"solution":null}',
            logIri: '/ai_logs/2',
        );

        $platform = $this->makeFakePlatform($platformResult);
        $parser = new AiJsonResponseParser();

        $result = $this->makeGenerator($em, $platform, $parser, $iriConverter)->generate(1);

        self::assertInstanceOf(ClosureSuggestionOutput::class, $result);
        self::assertNull($result->symptoms);
        self::assertNull($result->rootCause);
        self::assertNull($result->solution);
    }

    private function makeToc(): TechnicianOnCall
    {
        $serviceActivity = new ServiceActivity();
        $serviceActivity->name = 'Troubleshooting';

        $tocType = new TechnicianOnCallType();
        $tocType->name = 'toc.type.customer';

        $toc = new TechnicianOnCall();
        $toc->title = 'Test TOC';
        $toc->description = 'Test description';
        $toc->serviceActivity = $serviceActivity;
        $toc->technicianOnCallType = $tocType;

        return $toc;
    }

    private function makeComment(string $message): Comment
    {
        $comment = new Comment();
        $comment->setMessage($message);

        return $comment;
    }

    private function makeFakePlatform(PlatformResult $result): FakeTextPlatform
    {
        return new FakeTextPlatform(
            $this->createMock(PlatformInvokerInterface::class),
            $this->createMock(AILogFactory::class),
            $this->createMock(IriConverterInterface::class),
            $result,
        );
    }

    private function makeGenerator(
        ?EntityManagerInterface $em = null,
        ?TextPlatform $platform = null,
        ?AiJsonResponseParser $parser = null,
        ?IriConverterInterface $iriConverter = null,
    ): ClosureSuggestionGenerator {
        return new ClosureSuggestionGenerator(
            $em ?? $this->createMock(EntityManagerInterface::class),
            $platform ?? $this->makeFakePlatform(new PlatformResult(result: '{}', logIri: null)),
            $parser ?? new AiJsonResponseParser(),
            $iriConverter ?? $this->createMock(IriConverterInterface::class),
        );
    }
}

// Fake class to bypass the readonly restriction on TextPlatform
readonly class FakeTextPlatform extends TextPlatform
{
    public function __construct(
        PlatformInvokerInterface $invoker,
        AILogFactory $factory,
        IriConverterInterface $iriConverter,
        private PlatformResult $fakeResult,
    ) {
        parent::__construct($invoker, $factory, $iriConverter);
    }

    public function ask(
        PromptInterface $prompt,
        string $text,
        bool $createLog = false,
        string $model = 'mistral-small-latest'
    ): PlatformResult {
        return $this->fakeResult;
    }
}
