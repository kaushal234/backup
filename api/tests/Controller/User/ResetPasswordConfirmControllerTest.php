<?php

declare(strict_types=1);

namespace App\Tests\Controller\User;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Controller\User\ResetPasswordConfirmController;
use App\Entity\User;
use App\Manager\UserManager;
use App\Notifier\User\UserPasswordNotifier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordConfirmControllerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $decoder;
    private ObjectProphecy $entityManager;
    private ObjectProphecy $userManager;
    private ObjectProphecy $resetPasswordHelper;
    private ObjectProphecy $validator;
    private ObjectProphecy $notifier;
    private ResetPasswordConfirmController $controller;

    protected function setUp(): void
    {
        $this->decoder = $this->prophesize(DecoderInterface::class);
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->userManager = $this->prophesize(UserManager::class);
        $this->resetPasswordHelper = $this->prophesize(ResetPasswordHelperInterface::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->notifier = $this->prophesize(UserPasswordNotifier::class);

        $this->controller = new ResetPasswordConfirmController(
            $this->decoder->reveal(),
            $this->entityManager->reveal(),
            $this->userManager->reveal(),
            $this->resetPasswordHelper->reveal(),
            $this->validator->reveal(),
            $this->notifier->reveal(),
        );
    }

    public function testMissingNewPasswordThrows422(): void
    {
        $request = new Request([], [], [], [], [], [], '{}');
        $this->decoder->decode('{}', 'json')->willReturn([]);

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->controller->__invoke('1', 'some-token', $request);
    }

    public function testExpiredTokenReturns410(): void
    {
        $request = new Request([], [], [], [], [], [], '{"newPassword":"Sup3rSecret!!"}');
        $this->decoder->decode($request->getContent(), 'json')->willReturn(['newPassword' => 'Sup3rSecret!!']);

        $this->resetPasswordHelper->validateTokenAndFetchUser('expired-token')
            ->shouldBeCalledOnce()
            ->willThrow(ExpiredResetPasswordTokenException::class);

        try {
            $this->controller->__invoke('1', 'expired-token', $request);
            $this->fail('Expected HttpException to be thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(410, $exception->getStatusCode());
        }
    }

    public function testInvalidTokenReturns404(): void
    {
        $request = new Request([], [], [], [], [], [], '{"newPassword":"Sup3rSecret!!"}');
        $this->decoder->decode($request->getContent(), 'json')->willReturn(['newPassword' => 'Sup3rSecret!!']);

        $this->resetPasswordHelper->validateTokenAndFetchUser('invalid-token')
            ->shouldBeCalledOnce()
            ->willThrow(InvalidResetPasswordTokenException::class);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->__invoke('1', 'invalid-token', $request);
    }

    public function testValidTokenAndPasswordUpdatesAndConsumesToken(): void
    {
        $user = new User();
        $request = new Request([], [], [], [], [], [], '{"newPassword":"Sup3rSecret!!"}');
        $this->decoder->decode($request->getContent(), 'json')->willReturn(['newPassword' => 'Sup3rSecret!!']);

        $this->resetPasswordHelper->validateTokenAndFetchUser('valid-token')
            ->shouldBeCalledOnce()
            ->willReturn($user);

        $this->userManager->encodePassword($user)->shouldBeCalledOnce();

        $violations = new ConstraintViolationList();

        $this->validator->validateProperty($user, 'clearPassword', ['Default', 'password'])
            ->shouldBeCalledOnce()
            ->willReturn($violations);

        $this->userManager->logPassword($user)->shouldBeCalledOnce();
        $this->entityManager->flush()->shouldBeCalledOnce();

        $this->notifier->sendPasswordChanged($user)->shouldBeCalledOnce();

        $this->resetPasswordHelper->removeResetRequest('valid-token')->shouldBeCalledOnce();

        $response = $this->controller->__invoke('1', 'valid-token', $request);

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('Sup3rSecret!!', $user->getClearPassword());
    }

    public function testWeakPasswordDoesNotConsumeToken(): void
    {
        $user = new User();
        $request = new Request([], [], [], [], [], [], '{"newPassword":"weak"}');
        $this->decoder->decode($request->getContent(), 'json')->willReturn(['newPassword' => 'weak']);

        $this->resetPasswordHelper->validateTokenAndFetchUser('valid-token')
            ->shouldBeCalledOnce()
            ->willReturn($user);

        $this->userManager->encodePassword($user)->shouldBeCalledOnce();

        $violations = new ConstraintViolationList([
            new ConstraintViolation('Password too weak', null, [], $user, 'clearPassword', 'weak'),
        ]);

        $this->validator->validateProperty($user, 'clearPassword', ['Default', 'password'])
            ->shouldBeCalledOnce()
            ->willReturn($violations);

        $this->userManager->logPassword($user)->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();
        $this->notifier->sendPasswordChanged($user)->shouldNotBeCalled();
        $this->resetPasswordHelper->removeResetRequest('valid-token')->shouldNotBeCalled();

        $this->expectException(ValidationException::class);

        $this->controller->__invoke('1', 'valid-token', $request);
    }
}
