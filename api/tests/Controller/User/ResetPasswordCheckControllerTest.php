<?php

declare(strict_types=1);

namespace App\Tests\Controller\User;

use App\Controller\User\ResetPasswordCheckController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordCheckControllerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $entityManager;
    private ObjectProphecy $resetPasswordHelper;
    private ObjectProphecy $userRepository;
    private ResetPasswordCheckController $controller;

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->resetPasswordHelper = $this->prophesize(ResetPasswordHelperInterface::class);
        $this->userRepository = $this->prophesize(EntityRepository::class);

        $this->entityManager->getRepository(User::class)->willReturn($this->userRepository->reveal());

        $this->controller = new ResetPasswordCheckController(
            $this->entityManager->reveal(),
            $this->resetPasswordHelper->reveal(),
        );
    }

    public function testUserNotFoundThrows404(): void
    {
        $this->userRepository->findOneBy(['id' => '999'])->shouldBeCalledOnce()->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->__invoke('999', 'some-token');
    }

    public function testValidTokenReturns204WithoutSideEffect(): void
    {
        $user = new User();
        $this->userRepository->findOneBy(['id' => '1'])->shouldBeCalledOnce()->willReturn($user);

        $this->resetPasswordHelper->validateTokenAndFetchUser('valid-token')
            ->shouldBeCalledOnce()
            ->willReturn($user);
        $this->resetPasswordHelper->removeResetRequest('valid-token')->shouldNotBeCalled();
        $this->entityManager->flush()->shouldNotBeCalled();

        $response = $this->controller->__invoke('1', 'valid-token');

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testExpiredTokenReturns410(): void
    {
        $user = new User();
        $this->userRepository->findOneBy(['id' => '1'])->shouldBeCalledOnce()->willReturn($user);

        $this->resetPasswordHelper->validateTokenAndFetchUser('expired-token')
            ->shouldBeCalledOnce()
            ->willThrow(ExpiredResetPasswordTokenException::class);

        try {
            $this->controller->__invoke('1', 'expired-token');
            $this->fail('Expected HttpException to be thrown.');
        } catch (HttpException $exception) {
            $this->assertSame(410, $exception->getStatusCode());
        }
    }

    public function testInvalidTokenReturns404(): void
    {
        $user = new User();
        $this->userRepository->findOneBy(['id' => '1'])->shouldBeCalledOnce()->willReturn($user);

        $this->resetPasswordHelper->validateTokenAndFetchUser('invalid-token')
            ->shouldBeCalledOnce()
            ->willThrow(InvalidResetPasswordTokenException::class);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->__invoke('1', 'invalid-token');
    }
}
