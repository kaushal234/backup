<?php

declare(strict_types=1);

namespace tests\AppBundle\Twig\Extension;

use AppBundle\Twig\Extension\FileSizeExtension;
use PHPUnit\Framework\TestCase;

\define('KB', 1024);
\define('MB', KB * 1024);
\define('GB', MB * 1024);
\define('TB', GB * 1024);
\define('PB', TB * 1024);

class FileSizeExtensionTest extends TestCase
{
    protected $fse;

    protected function setUp(): void
    {
        $this->fse = new FileSizeExtension();
    }

    public function testReadableFilesize()
    {
        $this->assertSame('0 KB', $this->fse->readableFilesize(-1));
        $this->assertSame('0 KB', $this->fse->readableFilesize(0));
        $this->assertSame('1 byte', $this->fse->readableFilesize(1));
        $this->assertSame('2 bytes', $this->fse->readableFilesize(2));
        $this->assertSame('2 KB', $this->fse->readableFilesize(2 * KB));
        $this->assertSame('2.51 KB', $this->fse->readableFilesize(2.51 * KB));
        $this->assertSame('2.52 KB', $this->fse->readableFilesize(2.516 * KB));
        $this->assertSame('2 MB', $this->fse->readableFilesize(2 * MB));
        $this->assertSame('2 GB', $this->fse->readableFilesize(2 * GB));
        $this->assertSame('2 TB', $this->fse->readableFilesize(2 * TB));
        $this->assertSame('2 PB', $this->fse->readableFilesize(2 * PB));
    }

    public function testPrecision()
    {
        $this->assertSame(
            '1 KB', $this->fse->readableFilesize(1.2345 * KB, 0)
        );
        $this->assertSame(
            '1.23 KB', $this->fse->readableFilesize(1.2345 * KB, 2)
        );
        $this->assertSame(
            '1.235 KB', $this->fse->readableFilesize(1.2345 * KB, 3)
        );
    }

    public function testSeparator()
    {
        $this->assertSame(
            '2KB', $this->fse->readableFilesize(2 * KB, 0, '')
        );
    }
}
