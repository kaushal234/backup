<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop\BillOfMaterials;

use App\ION\Resources\IonTextByLanguage;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\IntranetViewItem;
use App\ION\Serializer\Denormalizer\Manufacturing\JobShop\ConflictItemTextsTrait;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class IntranetViewItemDenormalizer implements DenormalizerAwareInterface, DenormalizerInterface
{
    use ConflictItemTextsTrait;
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return IntranetViewItem::class === $type;
    }

    /**
     * @return array|mixed|object
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (empty($data)) {
            return null;
        }

        $item = new IntranetViewItem();

        $item->itemSelectionCode = IONXmlDecoder::trim($data['itemSelectionCode']);
        $item->level = (int) $data['level'];
        $item->partNumber = IONXmlDecoder::trim($data['partNumber']);
        $item->position = (int) $data['position'];
        $item->itemDescription = IONXmlDecoder::trim($data['itemDescription']);
        //        $item->itemOtherDescription = IONXmlDecoder::trim($data['itemOtherDescription']);
        $item->itemSignalCode = IONXmlDecoder::trim($data['itemSignalCode']);
        $item->engineeringDescription = IONXmlDecoder::trim($data['engineeringDescription']);
        $item->quantity = (float) $data['quantity'];
        $item->productQuantity = (float) $data['pbomQuantity'];
        $item->unitOfMeasure = IONXmlDecoder::trim($data['unitOfMeasure']);
        $item->engineeringRevisionEffectiveDate = $data['engineeringRevisionEffectiveDate'];
        $item->engineeringRevisionExpiryDate = $data['engineeringRevisionExpiryDate'];
        $item->partNumberProject = IONXmlDecoder::trim($data['partNumberProject']);
        $item->engineeringRevision = IONXmlDecoder::trim($data['engineeringRevision']);
        $item->engineeringSignalCode = $data['engineeringSignalCode'];
        $item->engineeringRevisionDescription = $data['engineeringRevisionDescription'];
        $item->extraInformation = $data['extraInformation'];
        $item->operation = (int) $data['operation'];
        $item->engineeringRevisionDrawing = IONXmlDecoder::trim($data['drawing'], true, '0');
        $item->pmoc = $data['pmoc'];
        $item->preventive = false !== mb_strpos($data['pmoc'] ?? '', 'p');
        $item->maintenance = false !== mb_strpos($data['pmoc'] ?? '', 'm');
        $item->overhaul = false !== mb_strpos($data['pmoc'] ?? '', 'o');
        $item->critical = false !== mb_strpos($data['pmoc'] ?? '', 'c');

        $data['children'] = IONXmlDecoder::enforceIndexedCollection($data['children']['item'] ?? []);
        foreach ($data['children'] as $child) {
            $item->addItem($this->denormalizer->denormalize($child, $type, $format, $context));
        }

        $data['textsByLanguage'] = IONXmlDecoder::enforceIndexedCollection($data['textsByLanguage']['textByLanguage'] ?? []);
        IONXmlDecoder::renameKey($data, 'textsByLanguage', 'textByLanguages');

        $textByLanguages = $this->denormalizer->denormalize($data['textByLanguages'], IonTextByLanguage::class.'[]', $format, $context);
        $item->setTextByLanguages(new ArrayCollection($textByLanguages));
        $this->denormalizeConflictItemTexts($data, $item, $format, $context);

        return $item;
    }
}
