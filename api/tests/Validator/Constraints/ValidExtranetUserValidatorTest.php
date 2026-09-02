<?php

declare(strict_types=1);

namespace App\Tests\Validator\Constraints;

use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserProfile;
use App\Validator\Constraints\ValidExtranetUser;
use App\Validator\Constraints\ValidExtranetUserValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class ValidExtranetUserValidatorTest extends ConstraintValidatorTestCase
{
    public function testNullValue(): void
    {
        $this->validator->validate(null, new ValidExtranetUser());

        $this->assertNoViolation();
    }

    public function testArchivedProfile(): void
    {
        $extranetUserProfile = new ExtranetUserProfile();
        $extranetUserProfile->archived = true;

        $extranetUser = new ExtranetUser();
        $extranetUser->setUsername('archived_user');
        $extranetUser->setExtranetUserProfile($extranetUserProfile);

        $this->validator->validate($extranetUser, new ValidExtranetUser());

        $this->buildViolation('toc.messages.errors.contact_disabled')
            ->setTranslationDomain('technician_on_call')
            ->setParameter('%extranetUser%', 'archived_user')
            ->assertRaised();
    }

    public function testValidUser(): void
    {
        $extranetUserProfile = new ExtranetUserProfile();

        $extranetUser = new ExtranetUser();
        $extranetUser->setUsername('valid_user');
        $extranetUser->setExtranetUserProfile($extranetUserProfile);

        $this->validator->validate($extranetUser, new ValidExtranetUser());

        $this->assertNoViolation();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ValidExtranetUserValidator();
    }
}
