<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\User;
use App\Entity\UserPasswordLog;
use App\Repository\UserPasswordLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PasswordValidator extends ConstraintValidator
{
    private readonly ?EntityManagerInterface $entityManager;
    private readonly ?UserPasswordHasherInterface $passwordHasher;

    public function __construct(?EntityManagerInterface $entityManager = null, ?UserPasswordHasherInterface $passwordHasher = null)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Password) {
            return;
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (0 !== preg_match('#(^\s|\s$)#', (string) $value)) {
            $this->context
                ->buildViolation($constraint->startingWithSpacemessage)
                ->addViolation();
        }

        if (!preg_match('#\pL#u', (string) $value)) {
            $this->context
                ->buildViolation($constraint->missingLettersMessage)
                ->addViolation();
        }

        if (
            // uppercase and lowercase
            (int) !preg_match('#(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})#u', (string) $value)
            // missing number
            + (int) !preg_match('#\d#u', (string) $value)
            // special characters
            + (int) !preg_match('#[^A-Za-z0-9]#u', (string) $value) > 1
        ) {
            $this->context
                ->buildViolation($constraint->requireTwoOfThreeConstraints)
                ->addViolation();
        }

        $user = $this->context->getObject();
        // Creation case we don't want more control on password
        if ($user instanceof User && null === $user->getId()) {
            return;
        }

        if (!$user instanceof User) {
            return;
        }

        $nameSegments = [];
        foreach ([$user->getFirstname(), $user->getLastname()] as $namePart) {
            $length = mb_strlen($namePart);

            if ($length < 3) {
                continue;
            }

            for ($i = 0; $i <= $length - 3; ++$i) {
                $nameSegments[] = mb_substr($namePart, $i, 3);
            }
        }

        if ([] !== $nameSegments && preg_match(\sprintf('/(%s)/i', implode('|', $nameSegments)), (string) $value)) {
            $this->context
                ->buildViolation($constraint->no3CommonLettersWithName)
                ->addViolation();
        }

        if (null === $this->entityManager) {
            return;
        }

        $uow = $this->entityManager->getUnitOfWork();
        $data = $uow->getOriginalEntityData($user) + ['salt' => '', 'encodedPassword' => ''];

        if ($this->passwordHasher->isPasswordValid((new User())->setSalt($data['salt'])->setEncodedPassword($data['encodedPassword']), $value)) {
            $this->context
                ->buildViolation($constraint->samePassword)
                ->addViolation();

            return;
        }

        /** @var UserPasswordLogRepository $userPasswordLogRepository */
        $userPasswordLogRepository = $this->entityManager->getRepository(UserPasswordLog::class);
        $previousPasswords = $userPasswordLogRepository->getPreviousPasswords($user, 2);

        foreach ($previousPasswords as $previousPassword) {
            if ($this->passwordHasher->isPasswordValid((new User())->setSalt($previousPassword->getSalt())->setEncodedPassword($previousPassword->getEncodedPassword()), $value)) {
                $this->context
                    ->buildViolation($constraint->samePassword)
                    ->addViolation();

                return;
            }
        }
    }
}
