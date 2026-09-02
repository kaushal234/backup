<?php

declare(strict_types=1);

namespace App\Controller\Support\EquipmentRecord;

use App\Entity\EquipmentRecord;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Symfony\Component\HttpFoundation\Response;

final class ExtranetQRCodeController
{
    public function __invoke(EquipmentRecord $equipmentRecord): Response
    {
        $payload = \sprintf(
            'https://extranet.tld-gse.com/public/%s',
            $equipmentRecord->getSerialNumber()
        );

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'outputBase64' => false,
            'scale' => 8,
            'quietzoneSize' => 0, // no margin
            'eccLevel' => QRCode::ECC_H,
        ]);

        $data = (new QRCode($options))->render($payload);
        $png = str_starts_with($data, 'data:')
            ? base64_decode(mb_substr($data, mb_strpos($data, ',') + 1), true)
            : $data;

        return new Response($png, Response::HTTP_OK, ['Content-Type' => 'image/png']);
    }
}
