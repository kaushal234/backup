<?php

declare(strict_types=1);

namespace App\Tests\FileSystem\Storage;

use App\FileSystem\Persistence\ContextProviders\FileAdapterContextProviderInterface;
use App\FileSystem\Storage\FileStorageHandlerFactory;
use App\FileSystem\Storage\FileStorageHandlerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;

class FileStorageHandlerTest extends KernelTestCase
{
    use ProphecyTrait;

    private FileStorageHandlerFactory $factory;
    private string $cacheDir;

    protected function setUp(): void
    {
        // Init container
        static::bootKernel([]);

        /** @var Filesystem $filesystem */
        $filesystem = static::getContainer()->get('filesystem');
        $this->cacheDir = static::$kernel->getCacheDir();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has(\stdClass::class)->shouldBeCalledTimes(1)->willReturn(true);
        $containerProphecy->get(\stdClass::class)->shouldBeCalledTimes(1)->willReturn(new class implements FileAdapterContextProviderInterface {
            public function processFile(File $file, array $metadata = [])
            {
                return null;
            }

            public static function getClass(): string
            {
                return '';
            }

            public function getFileConstraint($subject): ?Constraint
            {
                return null;
            }

            public function getFileProperty(): string
            {
                return 'property';
            }

            public function getDirectory(): string
            {
                return 'storage_handler';
            }

            public function getGeneratedFilename(object $subject, array $context): string
            {
                return 'filename';
            }
        });

        $this->factory = new FileStorageHandlerFactory($filesystem, new ParameterBag(['legacy.upload_dir' => $this->cacheDir]), $containerProphecy->reveal());
    }

    public function testHandlerInstantiationCreatesDirectory()
    {
        $handler = $this->factory->getStorageHandlerForClass(\stdClass::class);

        self::assertInstanceOf(FileStorageHandlerInterface::class, $handler);

        self::assertDirectoryExists($this->cacheDir.'/storage_handler');
    }

    public function testCreateAndRemoveFile()
    {
        $handler = $this->factory->getStorageHandlerForClass(\stdClass::class);

        self::assertFileDoesNotExist($this->cacheDir.\sprintf('/storage_handler/%s/testSaveFileWriteTheFile.jpg', (new \DateTime())->format('Y/m')));

        $handler->save('testSaveFileWriteTheFile.jpg', (new File('tests/fixtures/image_1200x1200.jpg'))->openFile());

        self::assertFileExists($this->cacheDir.\sprintf('/storage_handler/%s/testSaveFileWriteTheFile.jpg', (new \DateTime())->format('Y/m')));

        $handler->remove('testSaveFileWriteTheFile.jpg');

        self::assertFileDoesNotExist($this->cacheDir.\sprintf('/storage_handler/%s/testSaveFileWriteTheFile.jpg', (new \DateTime())->format('Y/m')));
    }
}
