<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Summarizer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Factory\AILogFactory;
use App\AI\Platform\Invoker\PlatformInvokerInterface;
use App\AI\Platform\TextPlatform;
use App\AI\Service\Extractor\GenericExtractor;
use App\AI\Service\Summarizer\GenericSummarizer;
use App\Entity\AI\AILog;
use App\Entity\AI\Request as AIRequest;
use App\Entity\DMS;
use App\Entity\Sales\SalesForecast;
use App\Entity\Service\TechnicianOnCall;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Message\MessageBag;

class GenericSummarizerTest extends TestCase
{
    private PlatformInvokerInterface&MockObject $invoker;
    private AILogFactory&MockObject $factory;
    private IriConverterInterface&MockObject $iriConverter;
    private TextPlatform $platform;

    protected function setUp(): void
    {
        $this->invoker = $this->createMock(PlatformInvokerInterface::class);
        $this->factory = $this->createMock(AILogFactory::class);
        $this->iriConverter = $this->createMock(IriConverterInterface::class);
        $this->platform = new TextPlatform($this->invoker, $this->factory, $this->iriConverter);
    }

    public function testSummarizeUsesGenericPromptPathByDefault(): void
    {
        $extractor = $this->createMock(GenericExtractor::class);
        $extractor->expects($this->once())
            ->method('extract')
            ->with(\stdClass::class, ['id' => 42])
            ->willReturn('extracted content');

        $this->factory->expects($this->once())
            ->method('createRequest')
            ->with('summarize/generic', ['id' => 42])
            ->willReturn(new AIRequest());

        $this->invoker->expects($this->once())
            ->method('invokeAsText')
            ->with(
                'mistral-small-latest',
                $this->callback(static function (MessageBag $bag): bool {
                    return 2 === \count($bag->getMessages());
                }),
            )
            ->willReturn('a summary');

        $this->factory->expects($this->never())->method('createLog');

        $summarizer = new GenericSummarizer($extractor, $this->platform);
        $output = $summarizer->summarize(\stdClass::class, ['id' => 42], false);

        $this->assertSame('a summary', $output->summary);
        $this->assertNull($output->logIri);
    }

    /**
     * @return iterable<string, array{class-string, string}>
     */
    public static function classToPromptPathProvider(): iterable
    {
        yield 'DMS' => [DMS::class, 'summarize/dms'];
        yield 'SalesForecast' => [SalesForecast::class, 'summarize/sfr'];
        yield TechnicianOnCall::MODULE_NAME => [TechnicianOnCall::class, 'summarize/toc'];
    }

    /**
     * @dataProvider classToPromptPathProvider
     *
     * @param class-string $class
     */
    public function testSummarizeUsesClassSpecificPromptPath(string $class, string $expectedPath): void
    {
        $extractor = $this->createMock(GenericExtractor::class);
        $extractor->method('extract')->willReturn('content');

        $request = new AIRequest();
        $log = new AILog();

        $this->factory->expects($this->once())
            ->method('createRequest')
            ->with($expectedPath, [])
            ->willReturn($request);

        $this->invoker->method('invokeAsText')->willReturn('summary');

        $this->factory->expects($this->once())
            ->method('createLog')
            ->with($request, 'summary')
            ->willReturn($log);

        $this->iriConverter->expects($this->once())
            ->method('getIriFromResource')
            ->with($log)
            ->willReturn('/api/ai_logs/7');

        $summarizer = new GenericSummarizer($extractor, $this->platform);
        $output = $summarizer->summarize($class, [], true);

        $this->assertSame('summary', $output->summary);
        $this->assertSame('/api/ai_logs/7', $output->logIri);
    }
}
