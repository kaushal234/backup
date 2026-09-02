<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordCheckController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
    ) {
    }

    public function __invoke($id, $token): Response
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $id]);

        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        try {
            $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ExpiredResetPasswordTokenException) {
            throw new HttpException(410, 'Token expired.');
        } catch (InvalidResetPasswordTokenException) {
            throw $this->createNotFoundException('Invalid token.');
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
