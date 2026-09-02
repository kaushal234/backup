<?php

declare(strict_types=1);

namespace App\Tests\LegacyBundle\Manager;

use Doctrine\DBAL\Connection;
use LegacyBundle\Manager\DMSManager;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Contracts\Cache\CacheInterface;

class DMSManagerStub extends DMSManager
{
    private readonly string $projectDir;

    public function __construct(Connection $legacyConnection, CacheInterface $arrayCache, string $legacyUploadDir, string $projectDir)
    {
        parent::__construct($legacyConnection, $arrayCache, $legacyUploadDir);
        $this->projectDir = $projectDir;
    }

    public function getDmsFile(int $dmsId): File
    {
        switch ($dmsId) {
            case 1234:
                return new File($this->projectDir.'/tests/fixtures/file.xls');
            case 2234:
                return new File($this->projectDir.'/tests/fixtures/file.zip');
            case 3234:
                return new File($this->projectDir.'/tests/fixtures/image_1200x1200.jpg');
            default:
                return new File($this->projectDir.'/tests/fixtures/file.pdf');
        }
    }
}
