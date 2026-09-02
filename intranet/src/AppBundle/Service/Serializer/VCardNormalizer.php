<?php

declare(strict_types=1);

namespace AppBundle\Service\Serializer;

use AppBundle\Form\Type\PhoneType;
use AppBundle\Manager\ImageManager;
use Sabre\VObject\Component\VCard;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VCardNormalizer implements NormalizerInterface, DenormalizerInterface
{
    protected ImageManager $imageManager;

    protected string $uploadDir;

    protected PropertyAccessor $propertyAccessor;

    public function __construct(ImageManager $imageManager, $uploadDir)
    {
        $this->imageManager = $imageManager;
        $this->uploadDir = $uploadDir;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            VCard::class => true,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return (array) $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof VCard;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        $vCard = new $class();

        switch ($data['@type']) {
            case 'People':
                $this->addPeople($vCard, $data->toArray());
                $this->addPhones($vCard, $data->toArray());
                $this->addPeopleAddresses($vCard, $data->toArray());
                $this->addPhoto($vCard, $data->toArray());
                break;
            case 'ExtranetUser':
                $this->addExtranetUser($vCard, $data->toArray());
                $this->addPhones($vCard, $data->toArray());
                $this->addExtranetUserAddresses($vCard, $data->toArray());
                break;
        }

        return $vCard;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return null !== $data && ('People' === $data['@type'] || 'ExtranetUser' === $data['@type']);
    }

    protected function getPeopleProperty(array $people, string $property)
    {
        if (!$this->propertyAccessor->isReadable($people, $property)) {
            return '';
        }

        return $this->propertyAccessor->getValue($people, $property);
    }

    /**
     * @param array $people
     */
    protected function addPeople(VCard $vCard, $people)
    {
        $vCard->add('N', [
            $this->getPeopleProperty($people, '[lastname]'),
            $this->getPeopleProperty($people, '[firstname]'),
        ]);
        $vCard->add('FN', $this->getPeopleProperty($people, '[firstname]').' '.$this->getPeopleProperty($people, '[lastname]'));
        $vCard->add('ORG', [
            $this->getPeopleProperty($people, '[businessUnit][name]'),
            $this->getPeopleProperty($people, '[department][name]'),
        ]);
        $vCard->add('TITLE', $this->getPeopleProperty($people, '[jobTitle]'));
        $vCard->add('URL', $this->getPeopleProperty($people, '[businessUnit][location][publicWebsite]') ?? '', ['type' => 'WORK']);
        $vCard->add('EMAIL', $this->getPeopleProperty($people, '[email]'), ['type' => 'INTERNET', 'PREF' => 1]);
    }

    /**
     * @param array $extranetUser
     */
    protected function addExtranetUser(VCard $vCard, $extranetUser)
    {
        $vCard->add('N', [
            $this->getPeopleProperty($extranetUser, '[lastname]'),
            $this->getPeopleProperty($extranetUser, '[firstname]'),
        ]);
        $vCard->add('FN', $this->getPeopleProperty($extranetUser, '[firstname]').' '.$this->getPeopleProperty($extranetUser, '[lastname]'));
        $vCard->add('ORG', [
            $this->getPeopleProperty($extranetUser, '[extranetUserProfile][customer][name]'),
            $this->getPeopleProperty($extranetUser, '[extranetUserProfile][division]'),
            $this->getPeopleProperty($extranetUser, '[extranetUserProfile][department]'),
        ]);
        $vCard->add('TITLE', $this->getPeopleProperty($extranetUser, '[extranetUserProfile][jobTitle]'));
        $vCard->add('EMAIL', $this->getPeopleProperty($extranetUser, '[email]'), ['type' => 'INTERNET', 'PREF' => 1]);
    }

    /**
     * @param array $people
     */
    protected function addPhoto(VCard $vCard, $people)
    {
        $photo = $this->getPeopleProperty($people, '[photo][filePath]');
        $path = "$this->uploadDir/$photo";

        if (!is_file($path)) {
            return;
        }

        $resizedPath = $this->imageManager->resize($path, 100);
        $vCard->add(
            'PHOTO',
            trim(base64_encode(file_get_contents($resizedPath))),
            ['type' => 'JPEG', 'encoding' => 'BASE64']
        );
    }

    /**
     * @param array $data
     */
    protected function addPhones(VCard $vCard, $data)
    {
        switch ($data['@type']) {
            case 'People':
                $phones = $this->getPeopleProperty($data, '[phones]');
                break;
            case 'ExtranetUser':
                $phones = $this->getPeopleProperty($data, '[extranetUserProfile][phones]');
                break;
            default:
                $phones = [];
        }

        if (empty($phones)) {
            return;
        }

        /** @see https://www.tld-gse.com/en/private/calendar/calendar.php?m[0]=tasks&m[1]=task&m[2]=view&id=1192665 */
        $preferedImportOrder = [
            PhoneType::TYPE_PHONE,
            PhoneType::TYPE_MOBILE,
            PhoneType::TYPE_HOME,
            PhoneType::TYPE_FAX,
            PhoneType::TYPE_RECEPTION,
        ];

        usort($phones, static fn ($a, $b) => (array_search($a['type'], $preferedImportOrder, true) < array_search($b['type'], $preferedImportOrder, true)) ? -1 : 1);

        foreach ($phones as $phone) {
            switch ($phone['type']) {
                case PhoneType::TYPE_RECEPTION:
                case PhoneType::TYPE_PHONE:
                    $params = ['type' => ['WORK', 'VOICE']];
                    break;
                case PhoneType::TYPE_HOME:
                    $params = ['type' => ['HOME', 'VOICE']];
                    break;
                case PhoneType::TYPE_MOBILE:
                    $params = ['type' => ['CELL', 'VOICE']];
                    break;
                case PhoneType::TYPE_FAX:
                    $params = ['type' => ['WORK', 'FAX']];
                    break;
                default:
                    $params = [];
            }
            $vCard->add('TEL', $phone['number'], $params);
        }
    }

    /**
     * @param array $people
     */
    protected function addPeopleAddresses(VCard $vCard, $people)
    {
        $location = $this->getPeopleProperty($people, '[businessUnit][location]');

        if (empty($location)) {
            return;
        }
        try {
            $countryName = Countries::getName((string) ($this->getPeopleProperty($location, '[address][country]') ?? ''));
        } catch (MissingResourceException $e) {
            $countryName = '';
        }

        $vCard->add('ADR', [
            '',
            $this->getPeopleProperty($location, '[company]'),
            $this->getPeopleProperty($location, '[address][street1]').' '.$this->getPeopleProperty($location, '[address][street2]'),
            $this->getPeopleProperty($location, '[address][city]'),
            $this->getPeopleProperty($location, '[address][state]'),
            $this->getPeopleProperty($location, '[address][postalCode]'),
            $countryName,
        ], ['type' => 'WORK']);

        $addressParts = $vCard->ADR->getParts();
        $address = <<<"ADDRESS"
$addressParts[0],
$addressParts[1] $addressParts[2],
$addressParts[3], $addressParts[4] $addressParts[5]
$addressParts[6]
ADDRESS;
        $vCard->add('ADR', $address, ['type' => 'WORK']);
    }

    /**
     * @param array $extranetUser
     */
    protected function addExtranetUserAddresses(VCard $vCard, $extranetUser)
    {
        $vCard->add('ADR', [
            '',
            $this->getPeopleProperty($extranetUser, '[company]'),
            $this->getPeopleProperty($extranetUser, '[extranetUserProfile][address][street1]')
            .' '
            .$this->getPeopleProperty($extranetUser, '[extranetUserProfile][address][street2]'),
            $this->getPeopleProperty($extranetUser, '[extranetUserProfile][address][city]'),
            $this->getPeopleProperty($extranetUser, '[extranetUserProfile][address][state]'),
            $this->getPeopleProperty($extranetUser, '[extranetUserProfile][address][postalCode]'),
            $this->getPeopleProperty($extranetUser, '[extranetUserProfile][country][name]'),
        ], ['type' => 'WORK']);

        $addressParts = $vCard->ADR->getParts();
        $address = <<<"ADDRESS"
$addressParts[0],
$addressParts[1] $addressParts[2],
$addressParts[3], $addressParts[4] $addressParts[5]
$addressParts[6]
ADDRESS;
        $vCard->add('ADR', $address, ['type' => 'WORK']);
    }
}
