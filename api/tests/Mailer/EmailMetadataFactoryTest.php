<?php

declare(strict_types=1);

namespace App\Tests\Mailer;

use App\Emailable\EmailMetadataFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailMetadataFactoryTest extends TestCase
{
    public function testGetAdapterSuccess()
    {
        $factory = new EmailMetadataFactory();
        $factory->registerAdapter(DummyEmailMetadataAdapter::class);

        self::assertInstanceOf(DummyEmailMetadataAdapter::class, $factory->getAdapter(new DummyObject('Dummy')));
    }

    public function testGetAdapterFailure()
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = new EmailMetadataFactory();
        $factory->registerAdapter(DummyEmailMetadataAdapter::class);

        //        $this->expectException(InvalidArgumentException::class);
        $factory->getAdapter(new DummyObject('Not dummy'));
    }
}
