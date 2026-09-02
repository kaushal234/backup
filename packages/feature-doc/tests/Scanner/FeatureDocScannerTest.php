<?php

declare(strict_types=1);

namespace Alvest\FeatureDoc\Tests\Scanner;

use Alvest\FeatureDoc\Scanner\FeatureDocScanner;
use PHPUnit\Framework\TestCase;

final class FeatureDocScannerTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = \sys_get_temp_dir().'/feature-doc-test-'.\uniqid();
        \mkdir($this->tmpDir);
        \mkdir($this->tmpDir.'/src');
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    public function testItFindsFeatureDocAttribute(): void
    {
        $phpFile = $this->tmpDir.'/src/TestClass.php';

        \file_put_contents($phpFile, <<<'PHP'
<?php

use Alvest\FeatureDoc\Attribute\FeatureDoc;

#[FeatureDoc('excel-export.md')]
class TestClass {}
PHP);

        $scanner = new FeatureDocScanner();
        $results = $scanner->scan($this->tmpDir.'/src');

        $this->assertCount(1, $results);
        $this->assertSame('excel-export.md', $results[0]['path']);
        $this->assertSame($phpFile, $results[0]['file']);
    }

    public function testItIgnoresNonLiteralArguments(): void
    {
        $phpFile = $this->tmpDir.'/src/TestClass.php';

        \file_put_contents($phpFile, <<<'PHP'
<?php

use Alvest\FeatureDoc\Attribute\FeatureDoc;

const DOC = 'excel-export.md';

#[FeatureDoc(DOC)]
class TestClass {}
PHP);

        $scanner = new FeatureDocScanner();
        $results = $scanner->scan($this->tmpDir.'/src');

        $this->assertCount(0, $results);
    }

    private function removeDir(string $dir): void
    {
        if (!\is_dir($dir)) {
            return;
        }

        foreach (\scandir($dir) as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $dir.'/'.$item;

            if (\is_dir($path)) {
                $this->removeDir($path);
            } else {
                \unlink($path);
            }
        }

        \rmdir($dir);
    }
}
