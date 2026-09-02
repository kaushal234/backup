<?php

declare(strict_types=1);

namespace App\Controller\Security\PasswordReset;

use App\DataTransferObject\Security\PasswordReset\PasswordResetRequest;
use App\Form\Type\Security\PasswordReset\PasswordResetRequestType;
use App\Http\Responder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route('/security/password-reset')]
final class IndexController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly FormFactoryInterface $factory,
    ) {
    }

    #[Route('', name: 'security:password-reset:index', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request): Response
    {
        $form = $this->factory->create(PasswordResetRequestType::class, new PasswordResetRequest());

        return $this->responder->render('security/password-reset/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
