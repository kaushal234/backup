<?php

declare(strict_types=1);

namespace Tests\AppBundle\Manager;

use ApiBundle\Client;
use AppBundle\Manager\FileManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManagerTest extends TestCase
{
    use ProphecyTrait;

    public function testUploadFileCallsClientRequest(): void
    {
        $clientProphecy = $this->prophesize(Client::class);
        $fileManager = new FileManager($clientProphecy->reveal());

        $object = ['@id' => '123'];
        $resource = 'upload';
        $imagePath = __DIR__.'/../Fixtures/test_image.jpg';
        $file = new UploadedFile($imagePath, 'test_image.jpg', null, null, true);

        $clientProphecy
            ->request(
                'upload',
                '123',
                'files',
                'POST',
                Argument::allOf(
                    Argument::withKey('headers'),
                    Argument::withKey('body')
                )
            )
            ->shouldBeCalledTimes(1);

        $fileManager->uploadFile($object, $file, $resource, 'test', 'files', false, true);

        $clientProphecy->checkProphecyMethodsPredictions();
    }
}
