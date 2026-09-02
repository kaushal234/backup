<?php

declare(strict_types=1);

namespace App\Tests\ION\Serializer\Encoder;

use App\ION\Serializer\Encoder\IONXmlDecoder;
use PHPUnit\Framework\TestCase;

class IONXmlDecoderTest extends TestCase
{
    /**
     * @dataProvider provideArrays
     */
    public function testArraysAreIndexedWhenNeeded(array $input, array $expectedOutput)
    {
        self::assertSame(IONXmlDecoder::enforceIndexedCollection($input), $expectedOutput);
    }

    public function provideArrays()
    {
        yield 'simple associative array' => [['Слава' => 'Україні!'], [['Слава' => 'Україні!']]];
        yield 'associative array with more keys' => [['Слава' => 'Україні!', 'на хуй' => ['путін', 'російський військовий корабель']], [['Слава' => 'Україні!', 'на хуй' => ['путін', 'російський військовий корабель']]]];
        yield 'non-associative array' => [['Слава', 'Україні!'], ['Слава', 'Україні!']];
        yield 'empty array' => [[], []];
    }

    public function testKeysAreRenamed()
    {
        $input = ['Arthur' => 'Perceval'];
        IONXmlDecoder::renameKey($input, 'Arthur', 'Karadoc');

        $this->assertArrayHasKey('Karadoc', $input, 'test existance of new key');
        $this->assertArrayNotHasKey('Arthur', $input, 'test old key is unset');
        $this->assertCount(1, $input, 'checking no extra data in array');
        $this->assertSame($input['Karadoc'], 'Perceval', 'test value does not change');
    }

    public function testRenameNonExistentKey()
    {
        $input = ['Arthur' => 'Perceval'];
        IONXmlDecoder::renameKey($input, 'Leodagan', 'Karadoc');

        $this->assertSame(['Arthur' => 'Perceval'], $input, 'test unchanged array');
    }

    /**
     * On LN, yes = "1" and no = "2".
     *
     * @return void
     */
    public function testEnforceYesNoToBooleanValue()
    {
        $this->assertTrue(IONXmlDecoder::enforceYesNoToBoolean('1'));
        $this->assertNotTrue(IONXmlDecoder::enforceYesNoToBoolean('2'));
        $this->assertNotTrue(IONXmlDecoder::enforceYesNoToBoolean(''));
        $this->assertNotTrue(IONXmlDecoder::enforceYesNoToBoolean('true'));
        $this->assertNotTrue(IONXmlDecoder::enforceYesNoToBoolean('false'));
        $this->assertNotTrue(IONXmlDecoder::enforceYesNoToBoolean('0'));
        $this->assertTrue(IONXmlDecoder::enforceYesNoToBoolean(' 1 '));
        $this->assertNotTrue(IONXmlDecoder::enforceYesNoToBoolean('null'));
    }

    /**
     * On LN, yes = "1" and no = "2".
     *
     * @return void
     */
    public function testConvertBooleanToYesNo()
    {
        $this->assertSame(1, IONXmlDecoder::convertBooleanToYesNo(true));
        $this->assertSame(2, IONXmlDecoder::convertBooleanToYesNo(false));
    }

    /**
     * @return void
     */
    public function testTrimReturnNullWithParameter()
    {
        $input = ['Arthur' => 'nullValue'];
        $this->assertNull(IONXmlDecoder::trim($input['Arthur'], true, 'nullValue'));
    }

    public function testConvertHtmlEntities()
    {
        $input = 'This is a <b>test</b> string.';
        $expectedOutput = 'This is a &lt;b&gt;test&lt;/b&gt; string.';
        $expectNotConvertHtmlEntities = "don't convert my html characters <b></b> .";

        $this->assertSame($expectedOutput, IONXmlDecoder::convertHtmlEntities($input));
        $this->assertSame($expectedOutput, IONXmlDecoder::trim($input));
        $this->assertSame($expectNotConvertHtmlEntities, IONXmlDecoder::trim($expectNotConvertHtmlEntities, false, null, false));
    }

    public function testGetLocalizedValue()
    {
        $input = [
            [
                '@languageID' => 'en',
                '#' => 'CABLE 48 AU 2X1.5 MM en en',
            ],
            [
                '@languageID' => 'tw_HK',
                '#' => 'CABLE 48 AU 2X1.5 MM en tw_HK',
            ],
            [
                '@languageID' => 'zh',
                '#' => 'CABLE 48 AU 2X1.5 MM en zh',
            ],
        ];

        $this->assertSame('CABLE 48 AU 2X1.5 MM en tw_HK', IONXmlDecoder::getLocalizedValue($input, 'tw_HK'));
        $this->assertSame('CABLE 48 AU 2X1.5 MM en en', IONXmlDecoder::getLocalizedValue($input));
        $this->assertSame('CABLE 48 AU 2X1.5 MM en en', IONXmlDecoder::getLocalizedValue($input, 'tirelipinpon'));
    }
}
