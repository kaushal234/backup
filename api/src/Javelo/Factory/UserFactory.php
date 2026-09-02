<?php

declare(strict_types=1);

namespace App\Javelo\Factory;

use App\Entity\Directory\People;
use App\Javelo\Resources\User;

class UserFactory
{
    public function createFromPeople(People $people, ?string $javeloId = null, array $synchronisedPeopleId = []): User
    {
        $javeloUser = new User();
        $javeloUser->id = $javeloId;
        $javeloUser->givenName = $people->getFirstname();
        $javeloUser->familyName = $people->getLastname();
        $javeloUser->userName = $people->getUsername();
        $javeloUser->active = !$people->isDisabled() && \in_array($people->getId(), array_column($synchronisedPeopleId, 'id'), true);
        $javeloUser->locale = \in_array($people->getLocale(), User::VALID_LOCALE, true) ? $people->getLocale() : User::DEFAULT_LOCALE;
        $javeloUser->title = $people->getJobTitle();
        $javeloUser->externalId = (string) $people->getId();
        $javeloUser->intranetId = (string) $people->getId();
        $javeloUser->department = $people->getDepartment()?->getName();
        $javeloUser->businessUnit = $people->getBusinessUnit()?->getName();
        $javeloUser->region = $people->getBusinessUnit()?->getRegion()?->getName();
        $javeloUser->subdivision = $people->getBusinessUnit()?->getRegion()?->getSubDivision()?->name;
        $javeloUser->division = $people->getBusinessUnit()?->getRegion()?->getSubDivision()?->division->name;
        // a disabled user can't have a manager or a disabled supervisor can't be set to a user or a supervisor not synchronize with javelo
        $javeloUser->managerUserName = $people->isDisabled() || null === $people->getSupervisor() || $people->getSupervisor()->isDisabled() || !\in_array($people->getSupervisor()->getId(), array_column($synchronisedPeopleId, 'id'), true)
            ? null
            : $people->getSupervisor()->getUsername();
        $javeloUser->gender = ($gender = $people->getGender()) === 'Mr' ? 'male' : ('Mrs' === $gender ? 'female' : '');
        $javeloUser->contractType = $people->getContractType()?->name;
        $javeloUser->setLastExitDate($people->getDisabledAt()?->format('Y-m-d'));
        $javeloUser->position = $people->getPosition()?->getDescription();
        $javeloUser->workingTime = (string) $people->getCoefficient();

        return $javeloUser;
    }
}
