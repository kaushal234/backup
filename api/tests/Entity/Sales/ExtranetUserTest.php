<?php

declare(strict_types=1);

namespace App\Tests\Entity\Sales;

use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserProfile;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExtranetUserTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;
    private UserManager $userManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->validator = $container->get(ValidatorInterface::class);
        $this->userManager = $container->get(UserManager::class);

        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->rollBack();
        }

        $this->entityManager->close();
        parent::tearDown();
    }

    public function testDefaultValidationAfterFlushDoesNotTriggerPasswordConstraint(): void
    {
        $profile = new ExtranetUserProfile();
        $profile->jobTitle = 'inspecteur';
        $profile->department = 'Dept';
        $profile->division = 'Div';
        $profile->sequenceIds = [];

        $email = 'test-'.uniqid().'@example.test';

        $extranetUser = new ExtranetUser();
        $extranetUser
            ->setExtranetUserProfile($profile)
            ->setEmail($email)
            ->setUsername($email)
            ->setFirstname('John')
            ->setLastname('Doe');

        $this->userManager->generatePassword($extranetUser);

        $this->entityManager->persist($extranetUser);
        $this->entityManager->flush();

        $this->assertIsInt($extranetUser->getId());

        $violations = $this->validator->validate($extranetUser);
        foreach ($violations as $violation) {
            $this->assertNotSame(
                'clearPassword',
                $violation->getPropertyPath(),
                'clearPassword should not be validated in Default group'
            );

            $this->assertStringNotContainsString(
                'Password must be different than the last 3 previous ones.',
                $violation->getMessage(),
                'Password samePassword constraint must not be triggered in Default group'
            );
        }
    }
}
