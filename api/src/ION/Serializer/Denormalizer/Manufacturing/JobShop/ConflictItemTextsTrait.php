<?php

declare(strict_types=1);

namespace App\ION\Serializer\Denormalizer\Manufacturing\JobShop;

use App\ION\Resources\IonTextByLanguage;
use App\ION\Serializer\Encoder\IONXmlDecoder;
use Doctrine\Common\Collections\ArrayCollection;

trait ConflictItemTextsTrait
{
    public function denormalizeConflictItemTexts($data, object $item, ?string $format, array $context): object
    {
        if (\array_key_exists('conflictItemText', $data) && !empty($data['conflictItemText'])) {
            $data['conflictItemText'] = IONXmlDecoder::enforceIndexedCollection($data['conflictItemText']['textsByLanguage']);
            IONXmlDecoder::renameKey($data, 'conflictItemText', 'conflictItemTexts');

            $conflictItemTexts = $this->denormalizer->denormalize($data['conflictItemTexts'], IonTextByLanguage::class.'[][]', $format, $context);
            $item->setConflictItemTexts(new ArrayCollection($conflictItemTexts));
        }

        return $item;
    }
}
