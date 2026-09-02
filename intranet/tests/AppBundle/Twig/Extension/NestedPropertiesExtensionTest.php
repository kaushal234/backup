<?php

declare(strict_types=1);

namespace tests\AppBundle\Twig\Extension;

use AppBundle\Twig\Extension\NestedPropertiesExtension;
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

    public function testASimplePropertyIsParsed()
    {
        $item = [
            'path' => 'ton chemin',
        ];

        self::assertSame(
            'ton chemin',
            $this->extension->parseNested($item, 'path')
        );
    }

    public function testANestedPropertyIsParsed()
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

    public function testAnIriIDIsParsed()
    {
        $item = [
            '@id' => '/c_est_ce_soir_le/5',
        ];

        self::assertSame(
            '5',
            $this->extension->parseNested($item, '@id')
        );
    }

    public function testANestedIriIDIsParsed()
    {
        $item = [
            'deep' => [
                'deeper' => [
                    'ouch' => [
                        '@id' => '/par/12',
                    ],
                ],
            ],
        ];

        self::assertSame(
            '12',
            $this->extension->parseNested($item, 'deep.deeper.ouch.@id')
        );
    }

    public function testNullIsReturnedWhenNotFound()
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
