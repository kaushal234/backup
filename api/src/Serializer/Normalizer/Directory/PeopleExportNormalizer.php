<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Directory;

use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Repository\Directory\PeopleRepository;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PeopleExportNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PEOPLE_EXPORT_NORMALIZER_ALREADY_CALLED';

    private array $mappedFields = [
        'id' => 'ID',
        'legacyId' => 'Legacy ID',
        'firstname' => 'Firstname',
        'lastname' => 'Lastname',
        'nickname' => 'Nickname',
        'email' => 'Email',
        'businessUnit' => 'Business unit',
        'legalEntity' => 'Legal entity',
        'department' => 'department',
        'position' => 'Position',
        'jobTitle' => 'Job title',
        'supervisor' => 'Supervisor',
        'mentor' => 'Mentor',
        'address' => 'Address',
        'premise' => 'Premise',
        'closestAirport' => 'Closest Airport',
        'phones' => 'Phones',
        'createdAt' => 'Created At',
        'disabledAt' => 'Disabled At',
        'supportTeam' => 'Support team',
        'division' => 'User Division',
        'intranetActivationDate' => 'Intranet Activation Date',
    ];

    private array $mappedRestrictedFields = [
        'contractType' => 'Contract type',
        'coefficient' => 'Coefficient',
        'gender' => 'Gender',
        'disabled' => 'Disabled',
        'lastLogin' => 'Last login',
        'passwordUpdatedAt' => 'Password updated at',
    ];

    /**
     * Fields not mapped with entity and DB. Calculated after.
     */
    private array $extraRestrictedFields = [
        'GG_EXCOM' => 'EXCOM member',
        'GG_EXCOM_supervisor' => 'Report to EXCOM member',
    ];

    private array $cachedGGExcomUser = [];

    private readonly AuthorizationCheckerInterface $authorizationChecker;
    private readonly PeopleRepository $peopleRepository;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, PeopleRepository $peopleRepository)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->peopleRepository = $peopleRepository;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof People && 'csv' === $format && null === ($context[self::ALREADY_CALLED] ?? null)
            && \in_array('people:export', $context[AbstractNormalizer::GROUPS] ?? [], true)
            && ($this->authorizationChecker->isGranted('FEATURE_DOWNLOAD_DIRECTORY') || $this->authorizationChecker->isGranted('FEATURE_DOWNLOAD_DIRECTORY_CONFIDENTIAL') || $this->authorizationChecker->isGranted('AUTHORIZED_APPLICATION_FEATURE_DOWNLOAD_DIRECTORY'));
    }

    /**
     * @param People $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $extraFields = [];
        $fieldsList = $this->mappedFields;
        if ($this->authorizationChecker->isGranted('FEATURE_DOWNLOAD_DIRECTORY_CONFIDENTIAL') || $this->authorizationChecker->isGranted('AUTHORIZED_APPLICATION_FEATURE_DOWNLOAD_DIRECTORY')) {
            $context[AbstractNormalizer::GROUPS][] = 'people:export:restricted';
            $extraFields = $this->extraRestrictedFields;
            $fieldsList = array_merge($this->mappedFields, $this->mappedRestrictedFields, $extraFields);

            if ([] === $this->cachedGGExcomUser) {
                $this->cachedGGExcomUser = $this->peopleRepository->findGroupMembersId('GG_EXCOM');
            }
        }

        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $normalizedData = array_merge($extraFields, $normalizedData);
        $normalizedData['division'] = $object->getBusinessUnit()?->getRegion()?->getSubDivision()?->division->name ?? '';
        $normalizedData['intranetActivationDate'] = $object->getEnableAt()?->format('Y-m-d') ?? '';
        $orderedNormalizeData = [];

        foreach ($fieldsList as $mappedKey => $header) {
            if (!\array_key_exists($mappedKey, $normalizedData)) {
                continue;
            }

            switch ($mappedKey) {
                case 'businessUnit':
                    $orderedNormalizeData[$header] = $normalizedData['businessUnit']['name'] ?? '';
                    break;
                case 'legalEntity':
                    $orderedNormalizeData[$header] = $normalizedData['legalEntity']['name'] ?? '';
                    break;
                case 'department':
                    $orderedNormalizeData[$header] = $normalizedData['department']['name'] ?? '';
                    break;
                case 'position':
                    $orderedNormalizeData[$header] = $normalizedData['position']['code'] ?? '';
                    break;
                case 'premise':
                    $orderedNormalizeData['Premise'] = $normalizedData['premise']['name'] ?? '';
                    $orderedNormalizeData['Support Team'] = $normalizedData['premise']['supportTeam']['name'] ?? '';
                    break;
                case 'closestAirport':
                    $orderedNormalizeData[$header] = $normalizedData['closestAirport']['code'] ?? '';
                    break;
                case 'supervisor':
                    $orderedNormalizeData['Supervisor firstname'] = $normalizedData['supervisor']['firstname'] ?? '';
                    $orderedNormalizeData['Supervisor lastname'] = $normalizedData['supervisor']['lastname'] ?? '';
                    $orderedNormalizeData['Supervisor email'] = $normalizedData['supervisor']['email'] ?? '';
                    break;
                case 'mentor':
                    $orderedNormalizeData['Mentor firstname'] = $normalizedData['mentor']['firstname'] ?? '';
                    $orderedNormalizeData['Mentor lastname'] = $normalizedData['mentor']['lastname'] ?? '';
                    $orderedNormalizeData['Mentor email'] = $normalizedData['mentor']['email'] ?? '';
                    break;
                case 'address':
                    $orderedNormalizeData[$header] = empty($normalizedData['address']) ? '' : $this->normalizeAddress($normalizedData['address']);
                    break;
                case 'phones':
                    foreach (Phone::getAvailableTypes() as $phoneType) {
                        $phoneHeader = ucfirst((string) $phoneType);
                        $orderedNormalizeData[$phoneHeader] = $this->normalizePhone($normalizedData[$mappedKey], $phoneType);
                    }
                    break;
                case 'contractType':
                    $orderedNormalizeData[$header] = $normalizedData['contractType']['name'] ?? '';
                    break;
                case 'GG_EXCOM':
                    $orderedNormalizeData[$header] = \in_array($normalizedData['id'], $this->cachedGGExcomUser, true) ? 'Y' : 'N';
                    break;
                case 'GG_EXCOM_supervisor':
                    $orderedNormalizeData[$header] = (!empty($normalizedData['supervisor']) && !empty($normalizedData['supervisor']['id']) && \in_array($normalizedData['supervisor']['id'], $this->cachedGGExcomUser, true)) ? 'Y' : 'N';
                    break;
                default:
                    $orderedNormalizeData[$header] = $normalizedData[$mappedKey];
            }
        }

        return $orderedNormalizeData;
    }

    private function normalizePhone(array $phones, string $type): string
    {
        foreach ($phones as $phone) {
            if ($phone['type'] === $type) {
                return $phone['number'];
            }
        }

        return '';
    }

    private function normalizeAddress(array $address): string
    {
        try {
            $countryName = Countries::getName($address['country'] ?? '');
        } catch (MissingResourceException $e) {
            $countryName = '';
        }

        return \sprintf(
            '%s %s %s, %s %s',
            $address['street1'],
            $address['street2'],
            $address['city'],
            $address['postalCode'],
            $countryName,
        );
    }
}
