<?php

declare(strict_types=1);

namespace App\Tests\FileSystem;

use App\Entity\Vault;
use App\FileSystem\VaultPartFileProvider;
use App\Repository\VaultRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class VaultPartFileProviderTest extends TestCase
{
    use ProphecyTrait;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/vault_test_'.uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testWhenSiteDoesNotExistGetFileReturnsNull()
    {
        $fileSystemProphecy = $this->prophesize(Filesystem::class);
        $fileSystemProphecy->exists(Argument::any())->shouldNotBeCalled();

        $vaultRepositoryMock = $this->getMockBuilder(VaultRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBySite'])->getMock();
        $vaultRepositoryMock->expects($this->once())->method('findOneBySite')->with(420)->willReturn(null);

        $provider = new VaultPartFileProvider($fileSystemProphecy->reveal(), $vaultRepositoryMock, $this->tempDir);
        $file = $provider->getFile(420, 'PART', 'XYZ', 'flexion');
        $this->assertNull($file);
    }

    public function testWhenSiteDoesNotExistGetFileByNameReturnsNull()
    {
        $fileSystemProphecy = $this->prophesize(Filesystem::class);
        $fileSystemProphecy->exists(Argument::any())->shouldNotBeCalled();

        $vaultRepositoryMock = $this->getMockBuilder(VaultRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBySite'])->getMock();
        $vaultRepositoryMock->expects($this->once())->method('findOneBySite')->with(420)->willReturn(null);

        $provider = new VaultPartFileProvider($fileSystemProphecy->reveal(), $vaultRepositoryMock, $this->tempDir);
        $file = $provider->getFileByName(420, 'XYZ');
        $this->assertNull($file);
    }

    public function testWhenNoFileWasFoundGetFileReturnsNull()
    {
        $fileSystemProphecy = $this->prophesize(Filesystem::class);
        $fileSystemProphecy->exists(Argument::any())->willReturn(false)->shouldBeCalledTimes(4);

        $vaultRepositoryMock = $this->getMockBuilder(VaultRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBySite'])->getMock();
        $vaultRepositoryMock->expects($this->once())->method('findOneBySite')->with(420)->willReturn($this->getVault('path', 1, 1));

        $provider = new VaultPartFileProvider($fileSystemProphecy->reveal(), $vaultRepositoryMock, $this->tempDir);
        $file = $provider->getFile(420, 'PART', 'XYZ', 'flexion');
        $this->assertNull($file);
    }

    public function testWhenNoFileWasFoundGetFileByNameReturnsNull()
    {
        $fileSystemProphecy = $this->prophesize(Filesystem::class);
        $fileSystemProphecy->exists(Argument::any())->willReturn(false)->shouldBeCalledTimes(4);

        $vaultRepositoryMock = $this->getMockBuilder(VaultRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBySite'])->getMock();
        $vaultRepositoryMock->expects($this->once())->method('findOneBySite')->with(420)->willReturn($this->getVault('path', 1, 1));

        $provider = new VaultPartFileProvider($fileSystemProphecy->reveal(), $vaultRepositoryMock, $this->tempDir);
        $file = $provider->getFileByName(420, 'XYZ');
        $this->assertNull($file);
    }

    /** @dataProvider provideFoldersConfiguration */
    public function testAFileObjectIsReturnedAsExpectedFromGivenName(array $paths, Vault $vault, string $partNumber, string $fileName)
    {
        $paths = $this->resolvePaths($paths);

        $correctPath = null;
        foreach ($paths as $path => $exists) {
            if ($exists) {
                $this->createFile($path);
                $correctPath = $path;
                break;
            }
        }

        $fileSystemMock = $this->getMockBuilder(Filesystem::class)->getMock();

        // Utiliser willReturnCallback au lieu de at()
        $pathsArray = array_keys($paths);
        $callCount = 0;
        $fileSystemMock->expects($this->any())
            ->method('exists')
            ->willReturnCallback(function ($path) use ($paths, &$callCount, $pathsArray) {
                $expectedPath = $pathsArray[$callCount] ?? null;
                $this->assertSame($expectedPath, $path, "Expected path at call $callCount");
                $result = $paths[$path] ?? false;
                ++$callCount;

                return $result;
            });

        $vaultRepositoryMock = $this->getMockBuilder(VaultRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBySite'])->getMock();
        $vaultRepositoryMock->expects($this->once())->method('findOneBySite')->with(420)->willReturn($vault);

        $provider = new VaultPartFileProvider($fileSystemMock, $vaultRepositoryMock, $this->tempDir);
        $file = $provider->getFileByName(420, $fileName);

        $this->assertInstanceOf(File::class, $file, 'No File was returned');
        $this->assertSame($correctPath, $file->getPathname(), 'The path in the file object is unexpected');
    }

    public function provideFoldersConfiguration()
    {
        yield 'Get file with full path and lowercase extension' => [[
            '{tempDir}/path/le/chien/PARTN/PA/PARTNUMBER_XYZ.flexion' => true,
            '{tempDir}/path/le/chien/PARTNUMBER_XYZ.flexion' => false,
        ], $this->getVault('path/le/chien', 5, 2), 'PARTNUMBER', 'PARTNUMBER_XYZ.flexion'];

        yield 'Get file with full path and uppercase extension' => [[
            '{tempDir}/path/le/chien/PARTN/PA/PARTNUMBER_XYZ.flexion' => false,
            '{tempDir}/path/le/chien/PARTNUMBER_XYZ.flexion' => true,
        ], $this->getVault('path/le/chien', 5, 2), 'PARTNUMBER', 'PARTNUMBER_XYZ.flexion'];
    }

    /**
     * @dataProvider providePathsConfiguration
     */
    public function testAFileObjectIsReturnedAsExpected(array $paths, Vault $vault, string $partNumber, string $revision, string $extension)
    {
        $paths = $this->resolvePaths($paths);

        $correctPath = null;
        foreach ($paths as $path => $exists) {
            if ($exists) {
                $this->createFile($path);
                $correctPath = $path;
                break;
            }
        }

        $fileSystemMock = $this->getMockBuilder(Filesystem::class)->getMock();

        // Utiliser willReturnCallback au lieu de at()
        $pathsArray = array_keys($paths);
        $callCount = 0;
        $fileSystemMock->expects($this->any())
            ->method('exists')
            ->willReturnCallback(function ($path) use ($paths, &$callCount, $pathsArray) {
                $expectedPath = $pathsArray[$callCount] ?? null;
                $this->assertSame($expectedPath, $path, "Expected path at call $callCount");
                $result = $paths[$path] ?? false;
                ++$callCount;

                return $result;
            });

        $vaultRepositoryMock = $this->getMockBuilder(VaultRepository::class)->disableOriginalConstructor()->onlyMethods(['findOneBySite'])->getMock();
        $vaultRepositoryMock->expects($this->once())->method('findOneBySite')->with(420)->willReturn($vault);

        $provider = new VaultPartFileProvider($fileSystemMock, $vaultRepositoryMock, $this->tempDir);
        $file = $provider->getFile(420, $partNumber, $revision, $extension);

        $this->assertInstanceOf(File::class, $file, 'No File was returned');
        $this->assertSame($correctPath, $file->getPathname(), 'The path in the file object is unexpected');
    }

    public function providePathsConfiguration()
    {
        yield 'Get file with full path and lowercase extension' => [[
            '{tempDir}/path/le/chien/PARTN/PA/PARTNUMBER_XYZ.flexion' => true,
            '{tempDir}/path/le/chien/PARTN/PA/PARTNUMBER_XYZ.FLEXION' => false,
            '{tempDir}/path/le/chien/PARTNUMBER_XYZ.flexion' => false,
            '{tempDir}/path/le/chien/PARTNUMBER_XYZ.FLEXION' => false,
        ], $this->getVault('path/le/chien', 5, 2), 'PARTNUMBER', 'XYZ', 'flexion'];

        yield 'Get file with full path and uppercase extension' => [[
            '{tempDir}/path/le/chien/PARTN/PA/PARTNUMBER_XYZ.flexion' => false,
            '{tempDir}/path/le/chien/PARTN/PA/PARTNUMBER_XYZ.FLEXION' => true,
            '{tempDir}/path/le/chien/PARTNUMBER_XYZ.flexion' => false,
            '{tempDir}/path/le/chien/PARTNUMBER_XYZ.FLEXION' => false,
        ], $this->getVault('path/le/chien', 5, 2), 'PARTNUMBER', 'XYZ', 'flexion'];

        yield 'Get file with default path and lowercase extension' => [[
            '{tempDir}/path/le/chien/PARTN/PA/PARTNUMBER_XYZ.flexion' => false,
            '{tempDir}/path/le/chien/PARTN/PA/PARTNUMBER_XYZ.FLEXION' => false,
            '{tempDir}/path/le/chien/PARTNUMBER_XYZ.flexion' => true,
            '{tempDir}/path/le/chien/PARTNUMBER_XYZ.FLEXION' => false,
        ], $this->getVault('path/le/chien', 5, 2), 'PARTNUMBER', 'XYZ', 'flexion'];

        yield 'Different lengths and mixed case on part number and extension' => [[
            '{tempDir}/le/chat/OUz/OUzE/OUzE_ABC.png' => false,
            '{tempDir}/le/chat/OUz/OUzE/OUzE_ABC.PNG' => false,
            '{tempDir}/le/chat/OUzE_ABC.png' => false,
            '{tempDir}/le/chat/OUzE_ABC.PNG' => true,
        ], $this->getVault('le/chat', 3, 4), 'OUzE', 'ABC', 'pNg'];

        yield 'Part number with blank spaces' => [[
            '{tempDir}/finder/123COU/123/123COUCOU_ABC.ext' => false,
            '{tempDir}/finder/123COU/123/123COUCOU_ABC.EXT' => false,
            '{tempDir}/finder/123COUCOU_ABC.ext' => false,
            '{tempDir}/finder/123COUCOU_ABC.EXT' => true,
        ], $this->getVault('finder', 6, 3), '   123COUCOU   ', 'ABC', 'ext'];

        yield 'The revision is REV' => [[
            '{tempDir}/path/le-oinj/PARTN/PA/PARTNUMBER.flexion' => false,
            '{tempDir}/path/le-oinj/PARTN/PA/PARTNUMBER.FLEXION' => false,
            '{tempDir}/path/le-oinj/PARTNUMBER.flexion' => false,
            '{tempDir}/path/le-oinj/PARTNUMBER.FLEXION' => true,
        ], $this->getVault('path/le-oinj', 5, 2), 'PARTNUMBER', 'REL', 'flexion'];

        yield 'Empty revision' => [[
            '{tempDir}/partout/PARTN/PA/PARTNUMBER.flexion' => false,
            '{tempDir}/partout/PARTN/PA/PARTNUMBER.FLEXION' => false,
            '{tempDir}/partout/PARTNUMBER.flexion' => false,
            '{tempDir}/partout/PARTNUMBER.FLEXION' => true,
        ], $this->getVault('partout', 5, 2), 'PARTNUMBER', '  . ..  ', 'flexion'];

        yield '3D zip file with full path and lowercase extension' => [[
            '{tempDir}/path/3d/PARTN/PA/PARTNUMBER_XYZ.zip' => true,
            '{tempDir}/path/3d/PARTN/PA/PARTNUMBER_XYZ.ZIP' => false,
            '{tempDir}/path/3d/PARTNUMBER_XYZ.zip' => false,
            '{tempDir}/path/3d/PARTNUMBER_XYZ.ZIP' => false,
        ], $this->getVault('path/3d', 5, 2), 'PARTNUMBER', 'XYZ', 'zip'];
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $dir.'/'.$item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    private function createFile(string $path): void
    {
        $dir = \dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, 'test content');
    }

    private function resolvePaths(array $paths): array
    {
        $resolved = [];
        foreach ($paths as $path => $exists) {
            $resolved[str_replace('{tempDir}', $this->tempDir, $path)] = $exists;
        }

        return $resolved;
    }

    private function getVault(string $path, int $folder, int $subFolder)
    {
        $vault = new Vault();
        $vault->path = $path;
        $vault->folder = $folder;
        $vault->subFolder = $subFolder;

        return $vault;
    }
}
