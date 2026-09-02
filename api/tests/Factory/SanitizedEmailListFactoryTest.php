<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Entity\User;
use App\Factory\SanitizedEmailListFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Component\Validator\Validation;

final class SanitizedEmailListFactoryTest extends TestCase
{
    private SanitizedEmailListFactory $factory;

    protected function setUp(): void
    {
        $validator = Validation::createValidator();
        $this->factory = new SanitizedEmailListFactory($validator);
    }

    public function testBuildCleanEmailAddressListKeepsValidStringsAndDropsInvalidOnes(): void
    {
        $result = $this->factory->buildCleanEmailAddressList([
            'valid@example.com',
            'not-an-email',
            '',
            null,
        ]);

        self::assertSame(['valid@example.com'], $result);
    }

    public function testBuildCleanEmailAddressListAcceptsUserAndReturnsItsEmail(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@example.com');

        $result = $this->factory->buildCleanEmailAddressList([$user]);

        self::assertSame(['user@example.com'], $result);
    }

    public function testBuildCleanEmailAddressListDeduplicates(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('duplicate@example.com');

        $result = $this->factory->buildCleanEmailAddressList([
            'duplicate@example.com',
            new Address('duplicate@example.com'),
            $user,
            'other@example.com',
            'duplicate@example.com',
        ]);

        self::assertSame(['duplicate@example.com', 'other@example.com'], $result);
    }

    public function testBuildCleanEmailAddressListIgnoresUnsupportedTypes(): void
    {
        $result = $this->factory->buildCleanEmailAddressList([
            123,
            new \stdClass(),
            ['array'],
            false,
        ]);

        self::assertSame([], $result);
    }
}
