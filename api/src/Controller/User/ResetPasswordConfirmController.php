<?php

declare(strict_types=1);

namespace App\Controller\User;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\User;
use App\Manager\UserManager;
use App\Notifier\User\UserPasswordNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordConfirmController extends AbstractController
{
    public function __construct(
        private readonly DecoderInterface $decoder,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserManager $userManager,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordNotifier $notifier,
    ) {
    }

    public function __invoke($id, $token, Request $request): Response
    {
        $content = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);

        if (!\is_array($content) || !\array_key_exists('newPassword', $content)) {
            throw new UnprocessableEntityHttpException('Property "newPassword" was expected in the body of the request.');
        }

        try {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ExpiredResetPasswordTokenException) {
            throw new HttpException(410, 'Token expired.');
        } catch (InvalidResetPasswordTokenException) {
            throw $this->createNotFoundException('Invalid token.');
        }

        $user->setClearPassword($content['newPassword']);
        $this->userManager->encodePassword($user);

        $violations = $this->validator->validateProperty($user, 'clearPassword', ['Default', 'password']);

        if (\count($violations) > 0) {
            throw new ValidationException($violations);
        }

        $this->userManager->logPassword($user);
        $this->entityManager->flush();

        $this->notifier->sendPasswordChanged($user);

        $this->resetPasswordHelper->removeResetRequest($token);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
