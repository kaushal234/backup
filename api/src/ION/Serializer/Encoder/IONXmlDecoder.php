<?php

declare(strict_types=1);

namespace App\ION\Serializer\Encoder;

use ApiPlatform\Metadata\GetCollection;
use App\ION\DataProvider\AbstractIONDataProvider;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class IONXmlDecoder extends XmlEncoder
{
    final public const FORMAT = 'ion_xml';
    final public const ION_OPERATION = 'ion_operation';
    final public const LOCALE_DEFAULT = 'en';

    public function decode(string $data, string $format, array $context = []): mixed
    {
        if ('' === mb_trim($data)) {
            return [];
        }

        // this if is here just to prevent using the crawler if it's not necessary
        if (false !== mb_strpos($data, 'xsi:nil="true"')) {
            $crawler = new Crawler();
            $crawler->addXmlContent($data);
            // This is a hack to remove the elements that are nullable and which value is empty
            // The Symfony Serializer is not able to handle that properly
            // Removing the elements is the only way to get a null value
            $crawler->filterXPath('//*[@xsi:nil="true"]')->each(static function (Crawler $crawler) {
                foreach ($crawler as $node) {
                    if ('' === $node->nodeValue) {
                        $node->parentNode->removeChild($node);
                    }
                }
            });

            $data = $crawler->outerHtml();
        }

        $decodedData = parent::decode($data, XmlEncoder::FORMAT, $context);
        $responseKey = $context[self::ION_OPERATION].'Response';
        $decodedData = $decodedData['S:Body'][$responseKey][$responseKey][AbstractIONDataProvider::DATA_AREA][$context[AbstractIONDataProvider::ION_RESOURCE_ATTRIBUTE]] ?? [];
        if ('' === $decodedData) {
            return [];
        }

        if (GetCollection::class === ($context['operation_type'] ?? null)) {
            return self::enforceIndexedCollection($decodedData);
        }

        return $decodedData;
    }

    public static function enforceIndexedCollection(array $collection): array
    {
        if ([] !== array_filter(array_keys($collection), 'is_string')) {
            return [$collection];
        }

        return $collection;
    }

    public static function renameKey(array &$array, string $key, string $newKey): void
    {
        if (!isset($array[$key])) {
            return;
        }
        $array[$newKey] = $array[$key];
        unset($array[$key]);
    }

    public static function trim(?string $value, bool $nullify = false, $nullValue = null, bool $convertHtmlEntities = true): ?string
    {
        $value = null !== $value ? mb_trim($value) : null;

        if (('' === $value || $nullValue === $value) && $nullify) {
            return null;
        }
        if (!$convertHtmlEntities) {
            return $value;
        }

        return self::convertHtmlEntities($value);
    }

    /**
     * On LN, yes = "1" and no = "2". We need to transform the value to be a boolean.
     */
    public static function enforceYesNoToBoolean(string $value): bool
    {
        return 1 === (int) $value;
    }

    public static function convertBooleanToYesNo(bool $value): int
    {
        return $value ? 1 : 2;
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }

    public static function convertHtmlEntities(string $value): string
    {
        return htmlentities($value);
    }

    public static function getLocalizedValue(array $propertyByLanguage, ?string $locale = null)
    {
        $availableLanguages = array_column($propertyByLanguage, '@languageID');
        if (!\in_array($locale, $availableLanguages, true)) {
            $locale = self::LOCALE_DEFAULT;
        }

        if ([] === array_column($propertyByLanguage, '@languageID')) {
            return '';
        }

        $keys = array_keys(array_column($propertyByLanguage, '@languageID'), $locale, true);

        return $propertyByLanguage[$keys[0]]['#'];
    }
}
