<?php

declare(strict_types=1);

namespace App\Tests\FileSystem\Zip;

use App\Entity\Sales\Customer;
use App\FileSystem\Zip\ContextProviders\ZipAdapterContextProviderInterface;
use App\FileSystem\Zip\Report\ZipReport;
use App\FileSystem\Zip\ZipAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;

class ZipAdapterTest extends TestCase
{
    use ProphecyTrait;

    public function testZipIsConstructedAndHasManifestFiles()
    {
        $file = new File(__DIR__.'/../../fixtures/file.txt');
        $containerInterface = $this->prophesize(ContainerInterface::class);
        $zipAdapterContextProviderProphecy = $this->prophesize(ZipAdapterContextProviderInterface::class);

        $zipAdapterContextProviderProphecy->processZip(Argument::type(\ZipArchive::class), [$file], Argument::type(ZipReport::class), false)->shouldBeCalledTimes(1);

        $contextProvider = new class($zipAdapterContextProviderProphecy->reveal()) implements ZipAdapterContextProviderInterface {
            public function __construct(
                private readonly ZipAdapterContextProviderInterface $decorated
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
                return Customer::class;
            }

            public function getFiles(object $subject, array $formats = []): array
            {
                return $this->decorated->getFiles($subject, $formats);
            }

            public function getArchiveName(object $subject): string
            {
                return 'toto';
            }
        };

        $zipAdapter = new ZipAdapter($contextProvider, $containerInterface->reveal());

        $filepath = tempnam(sys_get_temp_dir(), 'zip-test');
        $zip = $zipAdapter->createZip($filepath, [$file]);

        $zip->close();
        unlink($filepath);
    }
}
