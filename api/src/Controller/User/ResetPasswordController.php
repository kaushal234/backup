<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use App\Manager\UserManager;
use App\Notifier\User\UserPasswordNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private readonly DecoderInterface $decoder,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserManager $userManager,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly UserPasswordNotifier $notifier,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $content = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);

        if (!\is_array($content) || !\array_key_exists('email', $content) || !\array_key_exists(UserManager::LOGIN_PORTAL, $content)) {
            throw new UnprocessableEntityHttpException('Properties "email" and "portal" were expected in the body of the request.');
        }

        $loader = $this->userManager->getUserLoader($content[UserManager::LOGIN_PORTAL]);

        if (null === $loader) {
            throw new UnprocessableEntityHttpException('Property portal is invalid.');
        }

        $user = $loader($content['email']);

        if (!$user instanceof User) {
            throw new UserNotFoundException(\sprintf('User "%s" not found. Only user can reset their passwords.', $content['email']));
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (TooManyPasswordRequestsException) {
            throw new HttpException(Response::HTTP_TOO_MANY_REQUESTS, 'Too many password reset requests. Please try again later.');
        } catch (ResetPasswordExceptionInterface) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->notifier->sendConfirmation($user, $resetToken->getToken());
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
