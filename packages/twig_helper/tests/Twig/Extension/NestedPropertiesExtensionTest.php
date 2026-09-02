<?php

declare(strict_types=1);

namespace Alvest\TwigHelper\Tests\Twig\Extension;

use Alvest\TwigHelper\Twig\Extension\NestedPropertiesExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class NestedPropertiesExtensionTest extends TestCase
{
    /**
     * @var NestedPropertiesExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new NestedPropertiesExtension(new PropertyAccessor());
    }

    public function testASimplePropertyIsParsed(): void
    {
        $item = [
            'path' => 'ton chemin',
        ];

        self::assertSame(
            'ton chemin',
            $this->extension->parseNested($item, 'path')
        );
    }

    public function testANestedPropertyIsParsed(): void
    {
        $item = [
            'et' => [
                'path' => 'le chien',
            ],
        ];

        self::assertSame(
            'le chien',
            $this->extension->parseNested($item, 'et.path')
        );
    }

    public function testNullIsReturnedWhenNotFound(): void
    {
        $item = [
            'exists' => [
                'still_exists' => 'value',
            ],
        ];

        self::assertNull($this->extension->parseNested($item, 'does_not_exist'));
        self::assertNull($this->extension->parseNested($item, 'exists.does_not_exist'));
        self::assertNull($this->extension->parseNested($item, 'exists.still_exists.does_not_exist'));
    }
}
