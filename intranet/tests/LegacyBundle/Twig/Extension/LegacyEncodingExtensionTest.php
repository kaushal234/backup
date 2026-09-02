<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Twig\Extension;

use LegacyBundle\Twig\Extension\LegacyEncodingExtension;
use PHPUnit\Framework\TestCase;

class LegacyEncodingExtensionTest extends TestCase
{
    public function testThatTransIsConvertedToHTMLEntities()
    {
        $extension = new LegacyEncodingExtension();

        self::assertSame('&eacute;&agrave;&ocirc;&iuml; "entrec&ocirc;te" \'entrec&ocirc;te\' &#25105;&#29233;&#20013;&#22269;', $extension->legacyEncode('éàôï "entrecôte" \'entrecôte\' 我爱中国'));
    }
}
