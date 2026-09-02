<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Country;
use App\Entity\Directory\Phone;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserProfile;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\PhoneHelper;

class ExtranetUserFactory
{
    private readonly PhoneHelper $phoneHelper;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(PhoneHelper $phoneHelper, EntityManagerInterface $entityManager)
    {
        $this->phoneHelper = $phoneHelper;
        $this->entityManager = $entityManager;
    }

    public function __invoke(array $data): ExtranetUser
    {
        /** @var Country|null $country */
        $country = $this->entityManager->getRepository(Country::class)->findOneBy(['name' => $data['country']]);

        $extranetUserProfile = new ExtranetUserProfile();
        $extranetUserProfile->division = $data['division'];
        $extranetUserProfile->department = $data['department'];
        $extranetUserProfile->jobTitle = $data['title'];
        $extranetUserProfile->country = $country;
        $extranetUserProfile->companyName = $data['company'];
        $extranetUserProfile->archived = true;
        $extranetUserProfile->legacyAddress = $data['address'] ?? null;

        $extranetUser = new ExtranetUser();
        $extranetUser
            ->setExtranetUserProfile($extranetUserProfile)
            ->setUsername($data['email'])
            ->setEmail($data['email'])
            ->setLastname(mb_trim((string) $data['lastname']))
            ->setFirstname(mb_trim((string) $data['firstname']))
        ;

        $regions = [];
        if (null !== $country) {
            $regions[] = $country->getIsoCode2();
        }
        foreach (['phone' => Phone::TYPE_RECEPTION, 'direct-phone' => Phone::TYPE_PHONE, 'mobile' => Phone::TYPE_MOBILE, 'fax' => Phone::TYPE_FAX] as $column => $type) {
            if (null === ($data[$column] ?? null)) {
                continue;
            }
            $phone = $this->phoneHelper->parsePhone($data[$column], array_unique($regions));
            if (!$phone) {
                continue;
            }

            $extranetUser->addPhone((new Phone())->setType($type)->setNumber($phone));
        }

        return $extranetUser;
    }
}
