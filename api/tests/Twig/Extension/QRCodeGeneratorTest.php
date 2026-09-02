<?php

declare(strict_types=1);

namespace App\Tests\Twig\Extension;

use App\Twig\Extension\QRCodeGeneratorExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class QRCodeGeneratorTest extends KernelTestCase
{
    final public const TEMPLATE = '{{ generate_qrcode(\'1\')|raw }}';
    final public const BARCODE_HTML = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFcAAABXCAIAAAD+qk47AAAABnRSTlMA/wD/AP83WBt9AAAACXBIWXMAAA7EAAAOxAGVKw4bAAABtElEQVR4nO2ayWoEMQwFq0P+/5cnB4EvBiNZjjuQquMs7qF4Gi/y8/l8+Pd8vf0D/gRaAC0EWgAtBFoALQRaAC0EWgAtBN+ZDz3P03nGYquyGHl86/eePjALoIUgVRGD0jZ8TnIz/82nLzALoIWgVhGDvdzOb41xSgE+VTUDswBaCDYrYo85yYsauYlZAC0EVyvi1NbgOGYBtBBsVsTe4mQUwmJqyIx8vKtoFkALQa0ijv+3z4WQqZrjmAXQQvC8coujNCNcwCyAFoLz/Yi9c9TMZLH3SgazAFoIuvuIRQIzpZHpWVzYYpgF0EKQWjWVOmuLQmhODYtxmpgF0ELQrYjBXkO5dB7bPKFaYBZAC0F3Z72X7ZnSWVPmGMp9RBktQL9Dt9g+zJwqn9LvyWAWQAvBsVXTgr2lUbMx4RxRRgtw+e5r6eulpVFzQ2EWQAvB1buvzTlifuvUosssgBaCqzf9ZjIhz5SGZ00H0AK8XhGlfsSiNJqYBdBCcPXu62KcTPN6r8GXwSyAFoKX774O9voR7qxPogV46+7rX8MsgBYCLYAWAi2AFgItgBYCLYAWAi0A/ADpdAW8zBe6EQAAAABJRU5ErkJggg==';

    public function testQRCodeGenerationWithTwigFunction()
    {
        $twig = new Environment(new FilesystemLoader(), ['cache' => false]);
        $twig->addExtension(new QRCodeGeneratorExtension());
        $crawler = new Crawler($twig->createTemplate(self::TEMPLATE)->render());

        $this->assertSame($crawler->filter('body')->html(), self::BARCODE_HTML);
    }
}
