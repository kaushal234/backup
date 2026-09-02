<?php

declare(strict_types=1);

namespace App\Tests\Entity\Support;

use App\Entity\Support\Manual;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ManualTest extends KernelTestCase
{
    public function testValidateTheNonCriticalGroup()
    {
        $container = self::getContainer();
        $validator = $container->get(ValidatorInterface::class);
        $manual = new Manual();

        $errors = $validator->validate($manual, null, [Manual::NONCRITICAL_VALIDATION_GROUP]);

        $this->assertCount(1, $errors);
        $this->assertSame('No document found. The Manual should contain 1 or more.', $errors->get(0)->getMessage());
    }
}
