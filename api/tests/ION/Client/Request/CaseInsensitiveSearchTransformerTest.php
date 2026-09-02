<?php

declare(strict_types=1);

namespace App\Tests\ION\Client\Request;

use App\ION\Client\Request\CaseInsensitiveSearchTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CaseInsensitiveSearchTransformerTest extends KernelTestCase
{
    private CaseInsensitiveSearchTransformer $transformer;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->transformer = static::getContainer()->get(CaseInsensitiveSearchTransformer::class);
    }

    public function provideSearchStrings()
    {
        yield 'alphabet lowercase' => ['abcdefghijklmnopqrstuvwxyz', '[aA][bB][cC][dD][eE][fF][gG][hH][iI][jJ][kK][lL][mM][nN][oO][pP][qQ][rR][sS][tT][uU][vV][wW][xX][yY][zZ]'];
        yield 'alphabet uppercase' => ['ABCDEFGHIJKLMNOPQRSTUVWXYZ', '[Aa][Bb][Cc][Dd][Ee][Ff][Gg][Hh][Ii][Jj][Kk][Ll][Mm][Nn][Oo][Pp][Qq][Rr][Ss][Tt][Uu][Vv][Ww][Xx][Yy][Zz]'];
        yield 'numbers' => ['0123456789', '0123456789'];
        yield 'mixed' => ['AbuIOk14kIUY52QQ89', '[Aa][bB][uU][Ii][Oo][kK]14[kK][Ii][Uu][Yy]52[Qq][Qq]89'];
        yield 'special characters' => ['àèéÉËÇøô', '[àÀaA][èÈeE][éÉeE][ÉéEe][ËëEe][ÇçCc][øØoO][ôÔoO]'];
        yield 'non letters characters' => [';[/}+=@#$%', ';\/\}\+\=@\#\$%'];
    }

    /**
     * @dataProvider provideSearchStrings
     */
    public function testThatCharactersAreTransformed(string $searchString, string $expectedTransformerString)
    {
        $this->assertSame($expectedTransformerString, $this->transformer->transform($searchString));
    }
}
