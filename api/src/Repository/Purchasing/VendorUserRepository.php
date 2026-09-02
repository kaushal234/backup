<?php

declare(strict_types=1);

namespace App\Repository\Purchasing;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerContactManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContactCategory;
use App\Manager\UserManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VendorUserRepository extends ServiceEntityRepository
{
    private readonly UserManager $userManager;
    private readonly BusinessPartnerContactManager $contactManager;
    private ValidatorInterface $validator;

    public function __construct(
        ManagerRegistry $registry,
        UserManager $userManager,
        BusinessPartnerContactManager $contactManager,
        ValidatorInterface $validator
    ) {
        parent::__construct($registry, VendorUser::class);
        $this->userManager = $userManager;
        $this->contactManager = $contactManager;
        $this->validator = $validator;
    }

    public function findByUsername(string $username): ?VendorUser
    {
        $user = $this->findOneBy(['username' => $username]);
        if (null !== $user) {
            if ($user->isDisabled()) {
                if (null !== ($contact = $this->contactManager->findByEmail($username))
                    && $contact->isGrantedCategory(BusinessPartnerContactCategory::REQUIRED_CATEGORY_NAME)) {
                    $user
                        ->setHidden(false)
                        ->setDisabled(false)
                    ;
                    $entityManager = $this->getEntityManager();
                    $entityManager->flush();

                    return $user;
                }

                return null;
            }

            return $user;
        }

        if (null !== ($contact = $this->contactManager->findByEmail($username))) {
            $user = new VendorUser();
            $user
                ->setErpIdentifier($contact->contactCode)
                ->setEmail($contact->emailAddress)
                ->setUsername($contact->emailAddress)
                ->setHidden(false)
                ->setDisabled(false)
                ->setFirstname($contact->firstName)
                ->setLastname($contact->familyName)
            ;
            $this->userManager->generatePassword($user);

            $this->validator->validate($user);
            $entityManager = $this->getEntityManager();
            $entityManager->persist($user);

            $peopleRepository = $this->getEntityManager()->getRepository(People::class);
            if (null !== ($people = $peopleRepository->findOneBy(['email' => $contact->emailAddress]))) {
                $people->setVendorUserLinked($user);
                $entityManager->persist($people);
            }

            $entityManager->flush();

            return $user;
        }

        return null;
    }
}
