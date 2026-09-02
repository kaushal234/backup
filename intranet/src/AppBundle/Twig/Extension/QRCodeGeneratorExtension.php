<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class QRCodeGeneratorExtension extends AbstractExtension
{
    final public const DEFAULT_SCALE = 3;

    public function getFunctions(): array
    {
        return [
            new TwigFunction('generate_qrcode', $this->generateQRCode(...)),
        ];
    }

    public function generateQRCode(string $toEncode, int $scale = self::DEFAULT_SCALE): string
    {
        $qrcode = new QRCode(new QROptions([
            'scale' => $scale,
        ]));

        return $qrcode->render($toEncode);
    }
}
