<?php

declare(strict_types=1);

namespace App\Tests\Formatter\Snappy;

use App\Formatter\Snappy\Converter\PdfConverter;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PdfConverterTest extends KernelTestCase
{
    use ProphecyTrait;

    protected static $files = [
        'text',
        'image.png',
        'v1.1.pdf',
        'v1.2.pdf',
        'v1.3.pdf',
        'v1.4.pdf',
        'v1.5.pdf',
        'v1.6.pdf',
        'v1.7.pdf',
        'v2.0.pdf',
    ];

    protected $fixturesDir;

    protected $tmpDir;

    protected $stageDir;

    protected function setUp(): void
    {
        self::bootKernel();

        /** @var Filesystem $filesystem */
        $filesystem = static::getContainer()->get('filesystem');

        $this->fixturesDir = 'tests/fixtures/manuals_pdf/';
        $this->stageDir = sys_get_temp_dir().'/files/stage/';

        if (!file_exists($this->stageDir)) {
            $filesystem->mkdir($this->stageDir);
        }

        foreach (self::$files as $file) {
            $filesystem->copy($this->fixturesDir.$file, $this->stageDir.$file);
        }
    }

    protected function tearDown(): void
    {
        foreach (self::$files as $file) {
            unlink($this->stageDir.$file);
        }

        parent::tearDown();
    }

    /**
     * @dataProvider filesProvider
     */
    public function testGuessMethodMustReturnRightVersion(string $file, string $expectedVersion): void
    {
        $guesser = new PdfConverter($this->prophesize(Filesystem::class)->reveal());
        $version = $guesser->guess($file);

        $this->assertSame($version, $expectedVersion);
    }

    /**
     * @dataProvider invalidFilesProvider
     */
    public function testGuessMethodMustThrowException(string $file): void
    {
        $this->expectException(\RuntimeException::class);

        $guesser = new PdfConverter($this->prophesize(Filesystem::class)->reveal());
        $guesser->guess($file);
    }

    /**
     * @dataProvider filesToConvertProvider
     */
    public function testRunMethodMustConvertPDFVersionWithSuccess(string $file, string $newVersion): void
    {
        $tmpFile = $this->stageDir.'/'.uniqid('pdf_version_changer_test_', true).'.pdf';

        $converter = new PdfConverter(new Filesystem());
        $converter->run(
            $file,
            $tmpFile,
            $newVersion
        );

        $version = $converter->guess($tmpFile);

        $this->assertSame($version, $newVersion);
    }

    /**
     * @dataProvider invalidFilesToConvertProvider
     */
    public function testRunMethodMustThrowException(string $invalidFile, string $newVersion): void
    {
        $this->expectException(ProcessFailedException::class);

        $tmpFile = $this->stageDir.'/'.uniqid('pdf_version_changer_test_', true).'.pdf';

        $converter = new PdfConverter(new Filesystem());
        $converter->run(
            $invalidFile,
            $tmpFile,
            $newVersion
        );
    }

    /**
     * @dataProvider convertMethodMustCallRunCommandWithSuccessProvider
     */
    public function testConvertMethodMustCallRunCommandWithSuccess(string $tmpfile, bool $existsReturn, int $copyCalls, int $removeCalls): void
    {
        $fileSystemProphecy = $this->prophesize(Filesystem::class);

        $fileSystemProphecy->tempnam(Argument::cetera())->shouldBeCalledOnce()->willReturn($tmpfile);
        $fileSystemProphecy->exists(Argument::any())->shouldBeCalledOnce()->willReturn($existsReturn);
        $fileSystemProphecy->copy(Argument::cetera())->shouldBeCalledTimes($copyCalls);
        $fileSystemProphecy->remove(Argument::any())->shouldBeCalledTimes($removeCalls);

        $converter = new PdfConverter($fileSystemProphecy->reveal());
        if (!$existsReturn) {
            $this->expectException(\RuntimeException::class);
        }
        $converter->convert(sys_get_temp_dir().'/files/stage/v1.1.pdf', '1.4');
    }

    public function convertMethodMustCallRunCommandWithSuccessProvider(): array
    {
        $fileSystem = new Filesystem();

        return [
            [$fileSystem->tempnam('/tmp', 'pdf_version_changer_', '.pdf'), true, 1, 1],
            ['testbidon', false, 0, 0],
        ];
    }

    public function filesProvider(): array
    {
        return [
            [sys_get_temp_dir().'/files/stage/v1.1.pdf', '1.1'],
            [sys_get_temp_dir().'/files/stage/v1.2.pdf', '1.2'],
            [sys_get_temp_dir().'/files/stage/v1.3.pdf', '1.3'],
            [sys_get_temp_dir().'/files/stage/v1.4.pdf', '1.4'],
            [sys_get_temp_dir().'/files/stage/v1.5.pdf', '1.5'],
            [sys_get_temp_dir().'/files/stage/v1.6.pdf', '1.6'],
            [sys_get_temp_dir().'/files/stage/v1.7.pdf', '1.7'],
            [sys_get_temp_dir().'/files/stage/v2.0.pdf', '2.0'],
        ];
    }

    public static function invalidFilesProvider(): array
    {
        return [
            [sys_get_temp_dir().'/files/stage/text'],
            [sys_get_temp_dir().'/files/stage/image.png'],
            [sys_get_temp_dir().'/files/stage/dont-exists.pdf'],
        ];
    }

    public function filesToConvertProvider(): array
    {
        return [
            [sys_get_temp_dir().'/files/stage/v1.1.pdf', '1.7'],
            [sys_get_temp_dir().'/files/stage/v1.2.pdf', '2.0'],
            [sys_get_temp_dir().'/files/stage/v1.3.pdf', '1.5'],
            [sys_get_temp_dir().'/files/stage/v1.4.pdf', '1.4'],
            [sys_get_temp_dir().'/files/stage/v1.5.pdf', '1.4'],
            [sys_get_temp_dir().'/files/stage/v1.6.pdf', '1.4'],
            [sys_get_temp_dir().'/files/stage/v1.7.pdf', '1.4'],
            [sys_get_temp_dir().'/files/stage/v2.0.pdf', '1.4'],
        ];
    }

    public static function invalidFilesToConvertProvider(): array
    {
        return [
            [sys_get_temp_dir().'/files/stage/text', '1.4'],
            [sys_get_temp_dir().'/files/stage/image.png', '1.5'],
            [sys_get_temp_dir().'/files/stage/dont-exists.pdf', '1.5'],
        ];
    }
}
