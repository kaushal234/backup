<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Command\Helper;

use LegacyBundle\Command\Helper\SanitationHelper;
use PHPUnit\Framework\TestCase;

class SanitationHelperTest extends TestCase
{
    private ?SanitationHelper $helper = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helper = new SanitationHelper();
    }

    protected function tearDown(): void
    {
        $this->helper = null;
        parent::tearDown();
    }

    public function urlProvider()
    {
        return [
            'normal http url' => ['http://www.test.com', 'http://www.test.com'],
            'normal https url' => ['https://www.test.com', 'https://www.test.com'],
            'missing http' => ['www.test.com', 'http://www.test.com'],
            'starting with a slash' => ['/test.com', 'http://test.com'],
            'with trailling whitepaces' => [' http://www.test.com ', 'http://www.test.com'],
            'with an empty url' => ['', null],
            'with null' => [null, null],
        ];
    }

    /**
     * @dataProvider urlProvider
     */
    public function testNormalizeUrl($input, $expected)
    {
        self::assertSame($expected, $this->helper->normalizeUrl($input));
    }

    public function escapedNameProvider()
    {
        return [
            'simple quote multiple escaped' => ["AVIC XI\\\\'AN AIRCRAFT INDUSTRY(GROUP) CO .,LTD .", "AVIC XI'AN AIRCRAFT INDUSTRY(GROUP) CO .,LTD ."],
        ];
    }

    /**
     * @dataProvider escapedNameProvider
     */
    public function testUnescape($input, $expected)
    {
        self::assertSame($expected, $this->helper->unescape($input));
    }
}
