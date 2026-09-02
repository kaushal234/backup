<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Entity\User;
use App\Manager\UserManager;
use App\Validator\Constraints\Password;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Validation;

class UserManagerTest extends KernelTestCase
{
    use ProphecyTrait;

    private UserManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel([]);

        /** @var UserManager $manager */
        $manager = static::getContainer()->get(UserManager::class);
        $this->manager = $manager;
    }

    public function testThatGeneratedPasswordIsValid()
    {
        $user = new User();
        $this->manager->generatePassword($user);
        $callable = Validation::createIsValidCallable(new NotNull(), new Length(min: 15), new Password());

        self::assertTrue($callable($user->getClearPassword()));
    }
}
