<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller\Forecast\Download;

use App\Controller\Forecast\Download\DetailedController;
use App\Http\Responder;
use App\Sdk\ClientInterface;
use App\Sdk\Downloader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

final class DetailedControllerTest extends KernelTestCase
{
    private DetailedController $controller;

    protected function setUp(): void
    {
        $responder = new Responder(
            $this->createMock(Environment::class),
            $this->createMock(UrlGeneratorInterface::class),
            $this->createMock(SerializerInterface::class),
            new RequestStack()
        );

        $downloader = new Downloader(
            $this->createMock(ClientInterface::class),
            $responder,
            ''
        );

        $this->controller = new DetailedController($downloader);
    }

    public function testXlsxDownload(): void
    {
        $response = $this->controller;

        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame(__FILE__, $response->getFile()->getPathname());
        self::assertTrue($response->headers->has('content-disposition'));
        self::assertStringContainsString('forecast-detailed.xlsx', $response->headers->get('content-disposition'));
    }
}
