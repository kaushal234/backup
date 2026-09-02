<?php

declare(strict_types=1);

namespace tests\AppBundle\Twig\Extension;

use AppBundle\Twig\Extension\SortExtension;
use PHPUnit\Framework\TestCase;

class SortExtensionTest extends TestCase
{
    /**
     * @var SortExtension
     */
    private $extension;

    protected function setUp(): void
    {
        $this->extension = new SortExtension();
    }

    public function testArrayIsSorted()
    {
        $array = [
            [
                'name' => 'ACDC',
                'type' => 'Classic',
            ],
            [
                'name' => 'ZZ Top',
                'type' => 'Rap US',
            ],
            [
                'name' => 'ABBA',
                'type' => 'Rock n Roll',
            ],
        ];

        self::assertSame(
            [
                [
                    'name' => 'ABBA',
                    'type' => 'Rock n Roll',
                ],
                [
                    'name' => 'ACDC',
                    'type' => 'Classic',
                ],
                [
                    'name' => 'ZZ Top',
                    'type' => 'Rap US',
                ],
            ],
            $this->extension->sortBy($array, 'name')
        );
    }
}
