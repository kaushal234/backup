<?php

declare(strict_types=1);

namespace App\Tests\FileSystem\Zip;

use App\FileSystem\Zip\ContextProviders\ZipAdapterContextProviderInterface;
use App\FileSystem\Zip\ZipAdapter;
use App\FileSystem\Zip\ZipManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

class ZipManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testReturnsNoContentWhenNoFiles(): void
    {
        $object = new \stdClass();

        $contextProviderProphecy = $this->prophesize(ZipAdapterContextProviderInterface::class);
        $contextProviderProphecy
            ->getFiles($object, [])
            ->willReturn([]);

        $adapterProphecy = $this->prophesize(ZipAdapter::class);
        $adapterProphecy
            ->getContextProvider()
            ->willReturn($contextProviderProphecy->reveal());

        $manager = new ZipManager($adapterProphecy->reveal());

        $response = $manager->createZipResponse($object);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testReturnsZipResponseWhenFileExist(): void
    {
        $object = new \stdClass();
        $file = new File(__DIR__.'/../../fixtures/file.txt');

        $contextProvider = $this->prophesize(ZipAdapterContextProviderInterface::class);
        $contextProvider->getFiles($object, [])->willReturn([$file]);
        $adapter = $this->prophesize(ZipAdapter::class);
        $adapter
            ->getContextProvider()
            ->willReturn($contextProvider->reveal());

        $zip = new \ZipArchive();
        $zip->open('test', \ZipArchive::CREATE);
        $zip->addFile($file->getPathname());
        $adapter->createZip(Argument::any(), [$file], false)->shouldBeCalledOnce()->willReturn($zip);
        $contextProvider->getArchiveName($object)->shouldBeCalledOnce()->willReturn('alvest.zip');
        $manager = new ZipManager($adapter->reveal());

        $response = $manager->createZipResponse($object);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertInstanceOf(BinaryFileResponse::class, $response);

        unlink('test');
    }
}
