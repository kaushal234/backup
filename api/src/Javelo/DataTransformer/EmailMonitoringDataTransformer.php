<?php

declare(strict_types=1);

namespace App\Javelo\DataTransformer;

use ApiPlatform\Metadata\Exception\ItemNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Log;
use App\Entity\Directory\People;
use Symfony\Component\Form\DataTransformerInterface;

class EmailMonitoringDataTransformer implements DataTransformerInterface
{
    public function __construct(private readonly IriConverterInterface $iriConverter)
    {
    }

    public function transform(mixed $value): ?array
    {
        if (!$value instanceof Log) {
            return null;
        }

        try {
            $user = $this->iriConverter->getResourceFromIri($value->getResource());
        } catch (ItemNotFoundException $e) {
            return null;
        }

        /* @var People $user */
        return [
            'createdAt' => [$value->getCreatedAt()->format('Y-m-d')],
            'intranetId' => \array_key_exists('intranetId', $value->getChangeSet()) ? $value->getChangeSet()['intranetId'] : [$user->getId()],
            'action' => $value->getAction(),
            'poster' => $value->getUser()?->getId(),
            'title' => \array_key_exists('title', $value->getChangeSet()) ? $value->getChangeSet()['title'] : [$user->getJobTitle()],
            'userName' => \array_key_exists('userName', $value->getChangeSet()) ? $value->getChangeSet()['userName'] : [$user->getUsername()],
            'familyName' => \array_key_exists('familyName', $value->getChangeSet()) ? $value->getChangeSet()['familyName'] : [$user->getLastname()],
            'givenName' => \array_key_exists('givenName', $value->getChangeSet()) ? $value->getChangeSet()['givenName'] : [$user->getFirstname()],
            'locale' => \array_key_exists('locale', $value->getChangeSet()) ? $value->getChangeSet()['locale'] : [$user->getLocale()],
            'active' => \array_key_exists('active', $value->getChangeSet()) ? $value->getChangeSet()['active'] : [!$user->isDisabled()],
            'department' => \array_key_exists('department', $value->getChangeSet()) ? $value->getChangeSet()['department'] : [$user->getDepartment()?->getName()],
            'businessUnit' => \array_key_exists('businessUnit', $value->getChangeSet()) ? $value->getChangeSet()['businessUnit'] : [$user->getBusinessUnit()?->getName()],
            'region' => \array_key_exists('region', $value->getChangeSet()) ? $value->getChangeSet()['region'] : [$user->getBusinessUnit()?->getRegion()?->getName()],
            'subdivision' => \array_key_exists('subdivision', $value->getChangeSet()) ? $value->getChangeSet()['subdivision'] : [$user->getBusinessUnit()?->getRegion()?->getSubDivision()?->name],
            'division' => \array_key_exists('division', $value->getChangeSet()) ? $value->getChangeSet()['division'] : [$user->getBusinessUnit()?->getRegion()?->getSubDivision()?->division->name],
            'managerUserName' => \array_key_exists('managerUserName', $value->getChangeSet()) ? $value->getChangeSet()['managerUserName'] : [$user->getSupervisor()?->getUsername()],
            'gender' => \array_key_exists('gender', $value->getChangeSet()) ? $value->getChangeSet()['gender'] : [$user->getGender()],
            'contractType' => \array_key_exists('contractType', $value->getChangeSet()) ? $value->getChangeSet()['contractType'] : [$user->getContractType()?->name],
            'lastExitDate' => \array_key_exists('lastExitDate', $value->getChangeSet()) ? $value->getChangeSet()['lastExitDate'] : [$user->getDisabledAt()?->format('Y-m-d')],
            'position' => \array_key_exists('position', $value->getChangeSet()) ? $value->getChangeSet()['position'] : [$user->getPosition()?->getDescription()],
            'workingTime' => \array_key_exists('workingTime', $value->getChangeSet()) ? $value->getChangeSet()['workingTime'] : [$user->getCoefficient()],
        ];
    }

    public function reverseTransform($value): mixed
    {
        throw new \Exception('Not implemented');
    }
}
