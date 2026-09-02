<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Translation;

use LegacyBundle\Translation\Translator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class TranslatorTest extends TestCase
{
    use ProphecyTrait;

    public function testThatTransIsConvertedToHTMLEntities()
    {
        $decoratedProphecy = $this->prophesize(\Symfony\Bundle\FrameworkBundle\Translation\Translator::class);

        $decoratedProphecy->trans('id', ['%key%' => 'value'], 'ou_l_amour_sera_roi', 'qc')->shouldBeCalledTimes(1)->willReturn('éàôï "entrecôte" \'entrecôte\'');

        $translator = new Translator($decoratedProphecy->reveal());

        $translation = $translator->trans('id', ['%key%' => 'value'], 'ou_l_amour_sera_roi', 'qc');

        self::assertSame('&eacute;&agrave;&ocirc;&iuml; "entrec&ocirc;te" \'entrec&ocirc;te\'', $translation);
    }

    public function testThatLocaleAccessorsAreJustDecorated()
    {
        $decoratedProphecy = $this->prophesize(\Symfony\Bundle\FrameworkBundle\Translation\Translator::class);

        $decoratedProphecy->setLocale('yz')->shouldBeCalledTimes(1);
        $decoratedProphecy->getLocale()->shouldBeCalledTimes(1)->willReturn('yz');

        $translator = new Translator($decoratedProphecy->reveal());

        $translator->setLocale('yz');
        self::assertSame('yz', $translator->getLocale());
    }
}
