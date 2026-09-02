<?php

declare(strict_types=1);

namespace App\Tests\Unit\CQRS\QueryHandler\TechnicianOnCall;

use App\CQRS\Query\TechnicianOnCall\ExportTechnicianOnCallQuery;
use App\CQRS\QueryHandler\TechnicianOnCall\ExportTechnicianOnCallQueryHandler;
use App\Http\Responder;
use App\Sdk\Client;
use App\Sdk\DownloadedFile;
use App\Sdk\Downloader;
use App\Sdk\Resource\TechnicianOnCall;
use PHPUnit\Framework\TestCase;
use Psl\File\ReadHandleInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * @group unit
 */
class ExportTechnicianOnCallQueryHandlerTest extends TestCase
{
    public function test(): void
    {
        $handle = $this->createMock(ReadHandleInterface::class);
        $handle->method('getPath')->willReturn(__FILE__);
        $file = new DownloadedFile(200, ['content-type' => ['text/csv']], $handle);

        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('export')
            ->with(TechnicianOnCall::class, 'text/csv', ['columns' => 'id,title'])
            ->willReturn($file)
        ;

        $responder = new Responder($this->createMock(Environment::class), $this->createMock(UrlGeneratorInterface::class), new RequestStack());
        $handler = new ExportTechnicianOnCallQueryHandler(new Downloader($client, $responder, ''));

        $response = $handler(new ExportTechnicianOnCallQuery('technician_on_call.csv', 'text/csv', ['columns' => 'id,title']));

        $this->assertStringContainsString('technician_on_call.csv', (string) $response->headers->get('content-disposition'));
    }
}
