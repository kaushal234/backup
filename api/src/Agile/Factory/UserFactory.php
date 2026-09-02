<?php

declare(strict_types=1);

namespace App\Agile\Factory;

use App\Agile\Resources\User;
use App\Entity\Directory\People;
use App\Repository\Directory\PositionClassificationRepository;

class UserFactory
{
    public const VALID_LOCALE = [
        'zh-CN' => 'zh',
        'en' => 'en-gb',
    ];

    public function __construct(
        private readonly PositionClassificationRepository $positionClassificationRepository
    ) {
    }

    public function createFromPeople(People $people, bool $isNew, array $agileUsers): User
    {
        $positionCategory = $this->positionClassificationRepository->getPositionClassificationForPeople($people)?->positionCategory?->name;
        $managerAgileId = $people->getSupervisor()
            ? $this->searchManagerAgileIdFromPeople($people, $agileUsers)
            : null;
        $agileUser = new User();
        $agileUser->peopleId = (string) $people->getId();
        $agileUser->email = $people->getEmail();
        $agileUser->jobTitle = (string) $people->getJobTitle();
        $agileUser->firstName = (string) $people->getFirstname();
        $agileUser->lastName = (string) $people->getLastname();
        $agileUser->managerPeopleId = (string) $people->getSupervisor()?->getId();
        $agileUser->managerAgileId = (string) $managerAgileId;
        $agileUser->setActive($people->isDisabled() ? 'inactive' : 'active');
        $agileUser->setStartDate((string) $people->getCreatedAt()?->format(\DateTime::ATOM));
        $agileUser->timeZone = ($people->getBusinessUnit() && null !== $people->getBusinessUnit()->getLocation()->getTimeZone()) ? $people->getBusinessUnit()->getLocation()->getTimeZone() : 'America/New_York';
        $agileUser->languageCode = self::VALID_LOCALE[$people->getLocale()] ?? 'fr';
        $agileUser->position = (string) $people->getPosition()?->getDescription();
        $agileUser->positionCategory = (string) $positionCategory;
        $agileUser->department = (string) $people->getDepartment()?->getName();
        $agileUser->businessunit = (string) $people->getBusinessUnit()?->getName();
        $agileUser->region = (string) $people->getBusinessUnit()?->getRegion()?->getName();
        $agileUser->subdivision = (string) $people->getBusinessUnit()?->getRegion()?->getSubDivision()?->name;
        $agileUser->division = (string) $people->getBusinessUnit()?->getRegion()?->getSubDivision()?->division->name;
        $agileUser->address = mb_trim((string) $people->getAddress());
        $agileUser->contracttype = (string) $people->getContracttype()?->name;
        $agileUser->street1 = (string) $people->getAddress()->getStreet1();
        $agileUser->street2 = (string) $people->getAddress()->getStreet2();
        $agileUser->city = (string) $people->getAddress()->getCity();
        $agileUser->country = (string) $people->getAddress()->getCountry();
        $agileUser->zipcode = (string) $people->getAddress()->getPostalCode();
        $agileUser->loginMethod = User::LOGIN_METHOD;
        $agileUser->isNew = $isNew;

        return $agileUser;
    }

    /**
     * @param User[] $agileUsers
     */
    private function searchManagerAgileIdFromPeople(People $people, array $agileUsers): ?string
    {
        $manager = $people->getSupervisor();
        if (null === $manager) {
            return null;
        }
        foreach ($agileUsers as $agileId => $agileUser) {
            if ($agileUser->peopleId === (string) $manager->getId()) {
                return $agileId;
            }
        }

        return null;
    }
}
