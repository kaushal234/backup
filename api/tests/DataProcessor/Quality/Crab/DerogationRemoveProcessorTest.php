<?php

declare(strict_types=1);

namespace App\Tests\Unit\DataProcessor\Quality\Crab;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\State\ProcessorInterface;
use App\DataProcessor\Quality\Crab\DerogationRemoveProcessor;
use App\Entity\Quality\Crab;
use App\Entity\Quality\Derogation;
use App\Entity\Quality\DerogationFile;
use App\FileSystem\Persistence\PersistableFileManager;
use App\FileSystem\Persistence\PersistableFileManagerFactory;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DerogationRemoveProcessorTest extends TestCase
{
    private string $tmpDir;
    private string $tmpFilePath;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir();
        $this->tmpFilePath = $this->tmpDir.'/derogation_test_file.txt';
        file_put_contents($this->tmpFilePath, 'content');
    }

    protected function tearDown(): void
    {
        if (is_file($this->tmpFilePath)) {
            unlink($this->tmpFilePath);
        }
    }

    public function testProcessThrowsWhenDerogationIsAccepted(): void
    {
        $derogation = new Derogation();
        $derogation->setStatus(Derogation::ACCEPTED);

        $decorated = $this->createMock(ProcessorInterface::class);
        $decorated->expects($this->never())->method('process');

        $fileManagerFactory = $this->createMock(PersistableFileManagerFactory::class);
        $fileManagerFactory->expects($this->never())->method('getManagerForClass');

        $processor = new DerogationRemoveProcessor($decorated, $this->tmpDir, $fileManagerFactory);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Not possible to delete an accepted derogation.');

        $processor->process($derogation, new Delete());
    }

    public function testProcessDetachesCrabsAndFilesThenDelegatesToDecoratedProcessor(): void
    {
        $derogation = new Derogation();
        $derogation->setStatus(Derogation::OPEN);

        $crab = new Crab();
        $crab->derogation = $derogation;
        $this->setPrivateProperty($derogation, 'crabs', new ArrayCollection([$crab]));

        $derogationFile = $this->createMock(DerogationFile::class);
        $derogationFile->method('getFilePath')->willReturn('derogation_test_file.txt');
        $this->setPrivateProperty($derogation, 'files', new ArrayCollection([$derogationFile]));

        $fileManager = $this->createMock(PersistableFileManager::class);
        $fileManager->expects($this->once())
            ->method('detach')
            ->with(
                $derogation,
                $this->callback(fn (File $file) => $file->getPathname() === $this->tmpFilePath)
            );

        $fileManagerFactory = $this->createMock(PersistableFileManagerFactory::class);
        $fileManagerFactory->expects($this->once())
            ->method('getManagerForClass')
            ->with(DerogationFile::class)
            ->willReturn($fileManager);

        $decorated = $this->createMock(ProcessorInterface::class);
        $decorated->expects($this->once())
            ->method('process')
            ->with($derogation, $this->isInstanceOf(Delete::class), [], [])
            ->willReturn(null);

        $processor = new DerogationRemoveProcessor($decorated, $this->tmpDir, $fileManagerFactory);

        $processor->process($derogation, new Delete());

        $this->assertNull($crab->derogation);
    }

    public function testProcessSkipsMissingFilesWithoutThrowing(): void
    {
        $derogation = new Derogation();
        $derogation->setStatus(Derogation::OPEN);

        $this->setPrivateProperty($derogation, 'crabs', new ArrayCollection());

        $missingFile = $this->createMock(DerogationFile::class);
        $missingFile->method('getFilePath')->willReturn('does_not_exist.txt');
        $this->setPrivateProperty($derogation, 'files', new ArrayCollection([$missingFile]));

        $fileManagerFactory = $this->createMock(PersistableFileManagerFactory::class);
        $fileManagerFactory->expects($this->never())->method('getManagerForClass');

        $decorated = $this->createMock(ProcessorInterface::class);
        $decorated->expects($this->once())->method('process')->willReturn(null);

        $processor = new DerogationRemoveProcessor($decorated, $this->tmpDir, $fileManagerFactory);

        $processor->process($derogation, new Delete());
    }

    public function testProcessWorksWithNoCrabsAndNoFiles(): void
    {
        $derogation = new Derogation();
        $derogation->setStatus(Derogation::OPEN);

        $fileManagerFactory = $this->createMock(PersistableFileManagerFactory::class);
        $fileManagerFactory->expects($this->never())->method('getManagerForClass');

        $decorated = $this->createMock(ProcessorInterface::class);
        $decorated->expects($this->once())->method('process')->willReturn(null);

        $processor = new DerogationRemoveProcessor($decorated, $this->tmpDir, $fileManagerFactory);

        $processor->process($derogation, new Delete());
    }

    private function setPrivateProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }
}
