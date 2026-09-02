<?php

declare(strict_types=1);

namespace App\Tests\AI\Service;

use App\AI\Extractor\FileExtractorInterface;
use App\AI\Service\FileTextExtractor;
use App\Http\AzureDocumentIntelligenceClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileTextExtractorTest extends TestCase
{
    private AzureDocumentIntelligenceClient&MockObject $azureClient;
    private string $pdfFile;
    private string $pdfContent;

    protected function setUp(): void
    {
        $this->azureClient = $this->createMock(AzureDocumentIntelligenceClient::class);
        $this->pdfFile = __DIR__.'/../../fixtures/file.pdf';
        $this->pdfContent = (string) file_get_contents($this->pdfFile);
    }

    public function testReturnsExtractorOutputWhenLongEnough(): void
    {
        $pdfExtractor = $this->createMock(FileExtractorInterface::class);
        $pdfExtractor->method('supports')->with('application/pdf')->willReturn(true);
        $pdfExtractor->expects($this->once())
            ->method('extract')
            ->with($this->pdfFile)
            ->willReturn(str_repeat('A', 100));

        $this->azureClient->expects($this->never())->method('doRequest');

        $result = $this->buildExtractor([$pdfExtractor])->extract($this->pdfFile);

        $this->assertSame(str_repeat('A', 100), $result);
    }

    public function testFallsBackToAzureWhenExtractedTextIsTooShort(): void
    {
        $pdfExtractor = $this->createMock(FileExtractorInterface::class);
        $pdfExtractor->method('supports')->with('application/pdf')->willReturn(true);
        $pdfExtractor->method('extract')->willReturn('short');

        $this->azureClient->expects($this->once())
            ->method('doRequest')
            ->with($this->pdfContent)
            ->willReturn('OCR TEXT');

        $result = $this->buildExtractor([$pdfExtractor])->extract($this->pdfFile);

        $this->assertSame('OCR TEXT', $result);
    }

    public function testFallsBackToAzureWhenExtractorThrows(): void
    {
        $pdfExtractor = $this->createMock(FileExtractorInterface::class);
        $pdfExtractor->method('supports')->with('application/pdf')->willReturn(true);
        $pdfExtractor->method('extract')->willThrowException(new \RuntimeException());

        $this->azureClient->expects($this->once())
            ->method('doRequest')
            ->with($this->pdfContent)
            ->willReturn('OCR TEXT');

        $result = $this->buildExtractor([$pdfExtractor])->extract($this->pdfFile);

        $this->assertSame('OCR TEXT', $result);
    }

    public function testReturnsNullWhenAzureFails(): void
    {
        $pdfExtractor = $this->createMock(FileExtractorInterface::class);
        $pdfExtractor->method('supports')->with('application/pdf')->willReturn(true);
        $pdfExtractor->method('extract')->willReturn('short');

        $this->azureClient->method('doRequest')->willReturn(null);

        $this->assertNull($this->buildExtractor([$pdfExtractor])->extract($this->pdfFile));
    }

    public function testReturnsNullWhenFileDoesNotExist(): void
    {
        $pdfExtractor = $this->createMock(FileExtractorInterface::class);
        $pdfExtractor->expects($this->never())->method('supports');
        $this->azureClient->expects($this->never())->method('doRequest');

        $this->assertNull($this->buildExtractor([$pdfExtractor])->extract('/nonexistent/file.pdf'));
    }

    public function testFallsBackToAzureWhenNoExtractorSupportsTheMime(): void
    {
        $unsupporting = $this->createMock(FileExtractorInterface::class);
        $unsupporting->method('supports')->willReturn(false);
        $unsupporting->expects($this->never())->method('extract');

        $this->azureClient->expects($this->once())
            ->method('doRequest')
            ->willReturn('OCR TEXT');

        $result = $this->buildExtractor([$unsupporting])->extract($this->pdfFile);

        $this->assertSame('OCR TEXT', $result);
    }

    /**
     * @param list<FileExtractorInterface> $extractors
     */
    private function buildExtractor(array $extractors): FileTextExtractor
    {
        return new FileTextExtractor($extractors, $this->azureClient);
    }
}
