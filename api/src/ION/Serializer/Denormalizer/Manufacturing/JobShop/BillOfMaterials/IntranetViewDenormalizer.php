<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\IntranetMultiLevelView;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\IntranetView;
use App\ION\Serializer\Denormalizer\Manufacturing\JobShop\ConflictItemTextsTrait;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class IntranetViewDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use ConflictItemTextsTrait;
    use DenormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'BILL_OF_MATERIAL_INTRANET_VIEW_DENORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return (IntranetView::class === $type || IntranetMultiLevelView::class === $type) && !($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (empty($data)) {
            return null;
        }

        $data['project'] = IONXmlDecoder::trim($data['project']);
        $data['product'] = IONXmlDecoder::trim($data['product']);
        $data['itemDescription'] = IONXmlDecoder::trim($data['itemDescription']);
        //        $data['itemOtherDescription'] = IONXmlDecoder::trim($data['itemOtherDescription'] ?? '');
        $data['items'] = IONXmlDecoder::enforceIndexedCollection($data['items']['item'] ?? []);
        $data['preventive'] = false !== mb_strpos($data['pmoc'] ?? '', 'p');
        $data['maintenance'] = false !== mb_strpos($data['pmoc'] ?? '', 'm');
        $data['overhaul'] = false !== mb_strpos($data['pmoc'] ?? '', 'o');
        $data['critical'] = false !== mb_strpos($data['pmoc'] ?? '', 'c');
        $data['drawing'] = IONXmlDecoder::trim($data['drawing'], true, '0');
        $data['textsByLanguage'] = IONXmlDecoder::enforceIndexedCollection($data['textsByLanguage']['textByLanguage'] ?? []);
        IONXmlDecoder::renameKey($data, 'textsByLanguage', 'textByLanguages');

        $intranetView = $this->denormalizer->denormalize($data, $type, $format, $context);
        $this->denormalizeConflictItemTexts($data, $intranetView, $format, $context);

        return $intranetView;
    }
}
