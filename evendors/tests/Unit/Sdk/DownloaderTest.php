<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk;

use App\Http\Responder;
use App\Sdk\ClientInterface;
use App\Sdk\DownloadedFile;
use App\Sdk\Downloader;
use App\Sdk\Resource\Document;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psl\File\ReadHandleInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

/**
 * @group unit
 */
final class DownloaderTest extends TestCase
{
    private ClientInterface&MockObject $client;
    private Downloader $downloader;
    private TokenStorageInterface $tokenStorage;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $responder = new Responder($this->createMock(Environment::class), $this->createMock(UrlGeneratorInterface::class), $this->createMock(SerializerInterface::class), new RequestStack());

        $this->client = $this->createMock(ClientInterface::class);
        $this->downloader = new Downloader($this->client, $responder, '');
    }

    public function testSuccessfulDownload(): void
    {
        $headers = [
            'content-disposition' => ['inline; filename=foo.zip'],
            'content-type' => ['application/zip'],
            'x-bar' => ['foo'],
        ];

        $handle = $this->createMock(ReadHandleInterface::class);
        $handle->expects($this->once())->method('getPath')->willReturn(__FILE__);
        $file = new DownloadedFile(200, $headers, $handle);

        $this->client->expects($this->once())->method('download')->with(Document::class, 3)->willReturn($file);

        $response = $this->downloader->download(Document::class, '3');

        self::assertTrue($response->headers->has('content-disposition'));
        self::assertSame($headers['content-disposition'][0], $response->headers->get('content-disposition'));
        self::assertTrue($response->headers->has('content-type'));
        self::assertSame($headers['content-type'][0], $response->headers->get('content-type'));
        self::assertFalse($response->headers->has('x-bar'));
        self::assertSame(__FILE__, $response->getFile()->getRealPath());
    }

    public function testResourceIsNotFound(): void
    {
        $this->client->expects($this->once())->method('download')->willThrowException(new ClientException(new MockResponse(info: ['http_code' => 404])));

        $this->expectException(NotFoundHttpException::class);

        $this->downloader->download(Document::class, '3');
    }

    public function testFetchingResourceFails(): void
    {
        $this->client->expects($this->once())->method('download')->willThrowException(new ClientException(new MockResponse(info: ['http_code' => 400])));

        $this->expectException(ClientException::class);

        $this->downloader->download(Document::class, '3');
    }

    public function testSuccessfulStream(): void
    {
        $headers = [
            'content-disposition' => ['inline; filename=foo.zip'],
            'content-type' => ['application/zip'],
            'x-bar' => ['foo'],
        ];

        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'evendors-downloader-test');
        file_put_contents($temporaryFilePath, 'content');

        $handle = $this->createMock(ReadHandleInterface::class);
        $handle->expects($this->once())->method('getPath')->willReturn($temporaryFilePath);
        $file = new DownloadedFile(200, $headers, $handle);

        $this->client->expects($this->once())->method('download')->with(Document::class, ['foo' => 1, 'bar' => 2])->willReturn($file);
        $response = $this->downloader->stream(Document::class, ['foo' => 1, 'bar' => 2]);

        self::assertFileDoesNotExist($temporaryFilePath);
        self::assertTrue($response->headers->has('content-disposition'));
        self::assertSame($headers['content-disposition'][0], $response->headers->get('content-disposition'));
        self::assertTrue($response->headers->has('content-type'));
        self::assertSame($headers['content-type'][0], $response->headers->get('content-type'));
    }
}
