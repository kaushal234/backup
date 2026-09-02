<?php

declare(strict_types=1);

namespace App\Tests\Http;

use App\AI\Factory\AILogFactory;
use App\Entity\AI\Request as AIRequest;
use App\Entity\Sales\SalesForecast;
use App\Http\DeeplClient;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DeeplClientTest extends TestCase
{
    public const TESTABLE_COMMENTS_LIST = [
        'Est-ce que ça traduit ?',
        'Does it translate ?',
        'Übersetzt es?',
        '它是否翻译？',
    ];

    public function testGetTranslationDoesNotReachDeeplApiInTestEnvironment(): void
    {
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $factoryMock = $this->createMock(AILogFactory::class);
        $requestStackMock = $this->createMock(RequestStack::class);
        $deeplClient = new DeeplClient($httpClientMock, $loggerMock, $factoryMock, $requestStackMock);

        foreach (self::TESTABLE_COMMENTS_LIST as $comment) {
            $translation = $deeplClient->getTranslation($comment, 'EN', SalesForecast::class, 1);
            $expected = $comment;
            $this->assertSame($expected, $translation);
        }
    }

    public function testCreatesLogOnSuccessfulTranslation(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $responseMock->method('toArray')->willReturn(['translations' => [['text' => 'Translated']]]);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('request')->willReturn($responseMock);

        $aiRequestMock = $this->createMock(AIRequest::class);
        $factoryMock = $this->createMock(AILogFactory::class);
        $factoryMock->method('createRequest')->willReturn($aiRequestMock);
        $factoryMock->expects($this->once())
            ->method('createLog')
            ->with($aiRequestMock, 'Translated');

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getCurrentRequest')->willReturn(null);

        $deeplClient = new DeeplClient($httpClientMock, $this->createMock(LoggerInterface::class), $factoryMock, $requestStackMock);
        $deeplClient->getTranslation('Hello', 'FR', SalesForecast::class, 1);
    }

    public function testDoesNotCreateLogWhenCreateLogIsFalse(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $responseMock->method('toArray')->willReturn(['translations' => [['text' => 'Translated']]]);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('request')->willReturn($responseMock);

        $factoryMock = $this->createMock(AILogFactory::class);
        $factoryMock->method('createRequest')->willReturn($this->createMock(AIRequest::class));
        $factoryMock->expects($this->never())->method('createLog');

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getCurrentRequest')->willReturn(null);

        $deeplClient = new DeeplClient($httpClientMock, $this->createMock(LoggerInterface::class), $factoryMock, $requestStackMock);
        $deeplClient->getTranslation('Hello', 'FR', SalesForecast::class, 1, createLog: false);
    }

    public function testDoesNotCreateLogWhenQueryParamCreateLogIsFalse(): void
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $responseMock->method('toArray')->willReturn(['translations' => [['text' => 'Translated']]]);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $httpClientMock->method('request')->willReturn($responseMock);

        $factoryMock = $this->createMock(AILogFactory::class);
        $factoryMock->method('createRequest')->willReturn($this->createMock(AIRequest::class));
        $factoryMock->expects($this->never())->method('createLog');

        $requestStackMock = $this->createMock(RequestStack::class);
        $requestStackMock->method('getCurrentRequest')->willReturn(Request::create('/translate?createLog=false'));

        $deeplClient = new DeeplClient($httpClientMock, $this->createMock(LoggerInterface::class), $factoryMock, $requestStackMock);
        $deeplClient->getTranslation('Hello', 'FR', SalesForecast::class, 1);
    }
}
