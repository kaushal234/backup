<?php

declare(strict_types=1);

namespace App\Controller\TermsOfUse;

use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Twig\Environment;

use function sprintf;

#[Route('/terms-of-use')]
final class IndexController
{
    public function __construct(
        private readonly Responder $responder,
    ) {
    }

    #[Route('/', name: 'terms-of-use:index', methods: [Request::METHOD_GET])]
    public function __invoke(Environment $twig, LocaleAwareInterface $localeAware): Response
    {
        $loader = $twig->getLoader();
        $templateName = sprintf('terms-of-use/%s.html.twig', $localeAware->getLocale());

        // Default english terms of use
        if (!$loader->exists($templateName)) {
            return $this->responder->render('terms-of-use/en.html.twig');
        }

        return $this->responder->render($templateName);
    }
}
