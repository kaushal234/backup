<?php

declare(strict_types=1);

namespace App\Tests\Controller\File;

use App\Controller\File\ResizeController;
use App\Dto\FileInput;
use App\Entity\News\NewsFile;
use App\FileSystem\Image\ImageManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResizeControllerTest extends TestCase
{
    use ProphecyTrait;

    public function testResizeFile()
    {
        $legacyUploadDir = '/tmp';

        $file = new NewsFile();
        $file->setFilePath('test.jpg');

        $filePath = $legacyUploadDir.'/test.jpg';
        touch($filePath);

        $imageManagerProphecy = $this->prophesize(ImageManager::class);

        $fileObject = new \SplFileObject($filePath, 'w');
        $imageManagerProphecy->resize($fileObject, 100, 100)->willReturn('fake_binary_data');

        $fileInput = new FileInput();
        $fileInput->width = 100;
        $fileInput->height = 100;

        $request = new Request();
        $request->attributes->set('previous_data', $file);

        $controller = new ResizeController($imageManagerProphecy->reveal(), $legacyUploadDir);

        $response = $controller($fileInput, $request);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
