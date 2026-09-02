<?php

declare(strict_types=1);

namespace App\Tests\FileSystem\Zip;

use App\FileSystem\Zip\ContextProviders\ZipMultipleFoldersAdapterContextProviderInterface;
use App\FileSystem\Zip\Report\ZipReport;
use App\FileSystem\Zip\ZipAdapter;
use App\ION\Resources\Procurement\Orders\PurchaseOrder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ZipAdapterMultipleFilesTest extends TestCase
{
    use ProphecyTrait;

    public function testZipIsConstructedWithMultipPleFiles()
    {
        $files[] = [
            new File(__DIR__.'/../../fixtures/file1.txt'),
            new File(__DIR__.'/../../fixtures/file2.txt'),
            new File(__DIR__.'/../../fixtures/file3.txt'),
        ];

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $zipMultipleFolderAdapterContextProviderProphecy = $this->prophesize(ZipMultipleFoldersAdapterContextProviderInterface::class);

        $zipMultipleFolderAdapterContextProviderProphecy->processZip(Argument::type(\ZipArchive::class), $files, Argument::type(ZipReport::class), false)->shouldBeCalledTimes(1);

        $contextProvider = new class($zipMultipleFolderAdapterContextProviderProphecy->reveal()) implements ZipMultipleFoldersAdapterContextProviderInterface {
            public function __construct(
                private readonly ZipMultipleFoldersAdapterContextProviderInterface $decorated
            ) {
            }

            /**
             * {@inheritdoc}
             */
            public function processZip(\ZipArchive $zip, array $files, ZipReport $report, bool $flat = false)
            {
                $this->decorated->processZip($zip, $files, $report, $flat);
            }

            public static function getClass(): string
            {
                return PurchaseOrder::class;
            }

            public function getFiles(object $subject, array $formats = []): array
            {
                return $this->decorated->getFiles($subject, $formats);
            }

            public function getArchiveName(object $subject): string
            {
                return 'totoArchive';
            }

            public function getIterablePropertyPath(): string
            {
                return 'totoPath';
            }

            public function getFilenamePropertyPath(): string
            {
                return 'totoFile';
            }
        };

        $zipAdapter = new ZipAdapter($contextProvider, $serviceLocatorProphecy->reveal());

        $filepath = tempnam(sys_get_temp_dir(), 'zip-test');
        $zipAdapter->createZip($filepath, $files);
        unlink($filepath);
    }
}
