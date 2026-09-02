<?php

declare(strict_types=1);

namespace App\Controller\Locale;

use App\Http\Responder;
use App\Locale;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route('/locale')]
final class SwitchController
{
    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    #[Route('/switch', name: 'locale:switch', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request): Response
    {
        $locale = $request->request->get('locale');
        if (!Locale::isAvailable($locale)) {
            throw new BadRequestException();
        }

        $response = $this->responder->empty(Response::HTTP_ACCEPTED);
        $response->headers->setCookie(new Cookie('_locale', $locale));

        return $response;
    }
}
