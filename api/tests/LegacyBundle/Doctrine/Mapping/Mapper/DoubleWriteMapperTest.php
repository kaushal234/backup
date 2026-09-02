<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Doctrine\Mapping\Mapper;

use App\Entity\User;
use LegacyBundle\Doctrine\Mapping\Mapper\DoubleWriteMapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoubleWriteMapperTest extends KernelTestCase
{
    private DoubleWriteMapper $doubleWriteMapper;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel([]);

        /** @var DoubleWriteMapper $doubleWriteMapper */
        $doubleWriteMapper = static::getContainer()->get(DoubleWriteMapper::class);
        $this->doubleWriteMapper = $doubleWriteMapper;
    }

    public function testThatInheritedAnnotationAreRead()
    {
        $inherited = new class extends User {
        };

        $mapping = $this->doubleWriteMapper->getMapping(new \ReflectionClass($inherited));

        self::assertArrayHasKey('email', $mapping['columns']['email']);

        self::assertCount(1, $mapping['columns']['email']);
    }

    public function testThatInheritedAnnotationAreNotRead()
    {
        $inherited = new class extends User {
            #[\LegacyBundle\Doctrine\Mapping\Attributes\Column(column: 'youzeurnem')]
            #[\LegacyBundle\Doctrine\Mapping\Attributes\Column(column: 'ihmel')]
            protected ?string $username = null;
        };

        $mapping = $this->doubleWriteMapper->getMapping(new \ReflectionClass($inherited));

        self::assertArrayHasKey('youzeurnem', $mapping['columns']['username']);
        self::assertArrayHasKey('ihmel', $mapping['columns']['username']);
        self::assertArrayNotHasKey('email', $mapping['columns']['username']);

        self::assertCount(2, $mapping['columns']['username']);
    }

    public function testThatInheritedAnnotationWithSamePropertyAndColumnAreRead()
    {
        $inherited = new class extends User {
            #[\LegacyBundle\Doctrine\Mapping\Attributes\Column(column: 'email')]
            #[\LegacyBundle\Doctrine\Mapping\Attributes\Column(column: 'ihmel')]
            protected ?string $username = null;
        };

        $mapping = $this->doubleWriteMapper->getMapping(new \ReflectionClass($inherited));

        self::assertArrayHasKey('email', $mapping['columns']['username']);
        self::assertArrayHasKey('ihmel', $mapping['columns']['username']);

        self::assertCount(2, $mapping['columns']['username']);
    }
}
