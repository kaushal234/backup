<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Entity\User;
use App\Entity\UserPasswordLog;
use App\Repository\UserPasswordLogRepository;
use App\Validator\Constraints\Password;
use App\Validator\Constraints\PasswordValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class PasswordValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    private const PREVIOUS_PASSWORD = 'P@55word';

    private ObjectProphecy $passwordEncoderProphecy;
    private ObjectProphecy $entityManagerProphecy;
    private MockObject $userPasswordLogRepositoryProphecy;

    protected function setUp(): void
    {
        $this->entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);

        $unitOfWorkProphecy = $this->prophesize(UnitOfWork::class);
        $unitOfWorkProphecy->getOriginalEntityData(Argument::type(User::class))
            ->willReturn(['salt' => 'salt', 'encodedPassword' => 'encodedPassword']);

        $this->entityManagerProphecy->getUnitOfWork()
            ->willReturn($unitOfWorkProphecy);

        $this->userPasswordLogRepositoryProphecy = $this->getMockBuilder(UserPasswordLogRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPreviousPasswords'])
            ->getMock();

        $this->passwordEncoderProphecy = $this->prophesize(UserPasswordHasherInterface::class);
        $this->passwordEncoderProphecy->isPasswordValid(
            Argument::that(static fn (User $user) => 'salt' === $user->getSalt() && 'encodedPassword' === $user->getEncodedPassword()),
            Argument::any()
        )->willReturn(false);

        parent::setUp();
    }

    /**
     * @dataProvider providePasswords
     */
    public function testPasswordRequirements(string $password, ?string $violation, array $parameters = [])
    {
        $this->entityManagerProphecy->getRepository(UserPasswordLog::class)->shouldBeCalledTimes(1)->willReturn($this->userPasswordLogRepositoryProphecy);
        $this->userPasswordLogRepositoryProphecy->expects($this->once())->method('getPreviousPasswords')->with($this->callback(static fn ($user) => $user instanceof User), 2)->willReturn([(new UserPasswordLog())->setEncodedPassword('encodedPassword')->setSalt('salt')]);
        $this->entityManagerProphecy->getUnitOfWork()->shouldBeCalledTimes(1);

        $user = new class extends User {
            public function getId(): int
            {
                return 123;
            }
        };

        $user
            ->setFirstname('John')
            ->setLastname('Doe');

        $this->setObject($user);

        $this->validator->validate($password, new Password());

        if (null === $violation) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation($violation)
                ->atPath('property.path')
                ->setParameters($parameters)
                ->assertRaised()
            ;
        }
    }

    public function providePasswords(): \Generator
    {
        yield 'Initial space' => [' initial_Sp4ce', "A password can't start/finish with a space."];
        yield 'Final space' => ['final_Sp4ce ', "A password can't start/finish with a space."];
        yield 'Missing letter' => ['3123_90}=\/', 'Password must include at least one letter.'];
        yield 'Missing uppercase and number' => ['upp+rcase_[]', 'Password must check 2 of these constraints: include both upper and lower case letters, include at least one number, include at least one special character.'];
        yield 'Missing lowercase and number' => ['UPP+RCASE_[]', 'Password must check 2 of these constraints: include both upper and lower case letters, include at least one number, include at least one special character.'];
        yield 'Missing special character and lowercase' => ['UPPERC4SE34', 'Password must check 2 of these constraints: include both upper and lower case letters, include at least one number, include at least one special character.'];
        yield 'Missing special character and uppercase' => ['lowerc4se34', 'Password must check 2 of these constraints: include both upper and lower case letters, include at least one number, include at least one special character.'];
        yield 'Missing special character and number' => ['UpPerCaAaAaAse', 'Password must check 2 of these constraints: include both upper and lower case letters, include at least one number, include at least one special character.'];
        yield 'Valid Password 1' => ['UpPerCa@@AaAs3', null];
        yield 'Valid Password 2' => ['Ye@h\°/Secured', null];
        yield 'Valid Password 3' => ['{this IS s3cured}', null];
    }

    /**
     * @dataProvider providePasswordsAndUsers
     */
    public function testFirstNameOrLastNameParts(string $password, string $firstname, string $lastname, ?string $violation)
    {
        $this->entityManagerProphecy->getRepository(UserPasswordLog::class)->shouldBeCalledTimes(1)->willReturn($this->userPasswordLogRepositoryProphecy);
        $this->userPasswordLogRepositoryProphecy->expects($this->once())->method('getPreviousPasswords')->with($this->callback(static fn ($user) => $user instanceof User), 2)->willReturn([(new UserPasswordLog())->setEncodedPassword('encodedPassword')->setSalt('salt')]);
        $this->entityManagerProphecy->getUnitOfWork()->shouldBeCalledTimes(1);

        $user = new class extends User {
            public function getId(): int
            {
                return 123;
            }
        };

        $user
            ->setFirstname($firstname)
            ->setLastname($lastname);
        $this->setObject($user);

        $this->validator->validate($password, new Password());

        if (null === $violation) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation($violation)
                ->atPath('property.path')
                ->assertRaised()
            ;
        }
    }

    public function providePasswordsAndUsers()
    {
        yield 'Valid' => ['Couc0u!!@()', 'Abel, Yves & Ken', 'Fly', null];
        yield 'Invalid from first name' => ['Couc0u!!@()', 'Courloucoucou', 'Steuch-steuch', 'Password must not contain 3 consecutive letters from the first or last name.'];
        yield 'Invalid from last name' => ['Couc0u!!@()', 'Ah que', 'KouCou', 'Password must not contain 3 consecutive letters from the first or last name.'];
        yield 'Invalid even with different case' => ['sUp!!@()', 'Didier', 'IlestSuper', 'Password must not contain 3 consecutive letters from the first or last name.'];
    }

    public function testSamePassword()
    {
        $this->passwordEncoderProphecy->isPasswordValid(Argument::that(static fn (User $user) => 'salt' === $user->getSalt() && 'encodedPassword' === $user->getEncodedPassword()), self::PREVIOUS_PASSWORD)->shouldBeCalledTimes(1)->willReturn(true);
        $this->entityManagerProphecy->getRepository(UserPasswordLog::class)->shouldNotBeCalled();
        $this->userPasswordLogRepositoryProphecy->expects($this->never())->method('getPreviousPasswords');
        $this->entityManagerProphecy->getUnitOfWork()->shouldBeCalledTimes(1);

        $user = new class extends User {
            public function getId(): int
            {
                return 123;
            }
        };

        $user
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setClearPassword(self::PREVIOUS_PASSWORD);
        $this->setObject($user);

        $this->validator->validate(self::PREVIOUS_PASSWORD, new Password());

        $this->buildViolation('Password must be different than the last 3 previous ones.')
            ->atPath('property.path')
            ->assertRaised()
        ;
    }

    public function testNoUserInContextDoesNotHitNameAndHistoryChecks(): void
    {
        $this->entityManagerProphecy->getUnitOfWork()->shouldNotBeCalled();
        $this->entityManagerProphecy->getRepository(UserPasswordLog::class)->shouldNotBeCalled();
        $this->userPasswordLogRepositoryProphecy->expects($this->never())->method('getPreviousPasswords');
        $this->passwordEncoderProphecy->isPasswordValid(Argument::cetera())->shouldNotBeCalled();
        // Put a non-User object in the context
        $this->setObject(new \stdClass());

        // A password that passes the "simple" checks
        $this->validator->validate('UpPerCa@@AaAs3', new Password());

        $this->assertNoViolation();
    }

    public function testUserWithoutIdDoesNotHitNameAndHistoryChecks(): void
    {
        $this->entityManagerProphecy->getUnitOfWork()->shouldNotBeCalled();
        $this->entityManagerProphecy->getRepository(UserPasswordLog::class)->shouldNotBeCalled();
        $this->userPasswordLogRepositoryProphecy->expects($this->never())->method('getPreviousPasswords');
        $this->passwordEncoderProphecy->isPasswordValid(Argument::cetera())->shouldNotBeCalled();

        // User creation case: getId() returns null
        $user = new class extends User {
            public function getId(): ?int
            {
                return null;
            }
        };

        $user
            ->setFirstname('John')
            ->setLastname('Doe');

        $this->setObject($user);

        // A password that passes the "simple" checks
        $this->validator->validate('UpPerCa@@AaAs3', new Password());

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new PasswordValidator($this->entityManagerProphecy->reveal(), $this->passwordEncoderProphecy->reveal());
    }
}
