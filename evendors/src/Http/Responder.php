<?php

declare(strict_types=1);

namespace App\Http;

use Psl\Dict;
use SplFileInfo;
use Stringable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;

final class Responder
{
    public function __construct(
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SerializerInterface $serializer,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Flash a message of the given type for the next request.
     *
     * @param non-empty-string $type
     */
    public function flash(string $type, string $message): void
    {
        /** @var Session $session */
        $session = $this->requestStack->getCurrentRequest()?->getSession();

        $session->getFlashBag()->add($type, $message);
    }

    /**
     * Create an empty response.
     *
     * @param array<string, string|list<string>> $headers
     */
    public function empty(int $status = Response::HTTP_NO_CONTENT, array $headers = []): Response
    {
        return new Response(null, $status, $headers);
    }

    /**
     * Render the given Twig template and return an HTML response.
     *
     * @param array<string, mixed>               $context
     * @param array<string, string|list<string>> $headers
     */
    public function render(string|Stringable $template, array $context = [], int $status = Response::HTTP_OK, array $headers = []): Response
    {
        $content = $this->twig->render((string) $template, $context);
        $response = new Response($content, $status, $headers);

        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
        }

        return $response;
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param array<string, string|list<string>> $headers
     */
    public function redirect(string|Stringable $url, int $status = Response::HTTP_FOUND, array $headers = []): RedirectResponse
    {
        return new RedirectResponse((string) $url, $status, $headers);
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param array<array-key, scalar>    $parameters
     * @param array<string, list<string>> $headers
     */
    public function route(string|Stringable $route, array $parameters = [], int $status = Response::HTTP_FOUND, array $headers = []): RedirectResponse
    {
        $url = $this->urlGenerator->generate((string) $route, $parameters);

        return $this->redirect($url, $status, $headers);
    }

    /**
     * Returns a JsonResponse that uses the serializer component if enabled, or json_encode.
     *
     * @param array<string, mixed>|object        $data
     * @param array<string, string|list<string>> $headers
     * @param array<string, mixed>               $context
     */
    public function json(array|object $data, int $status = Response::HTTP_OK, array $headers = [], array $context = []): JsonResponse
    {
        $json = $this->serializer->serialize($data, 'json', Dict\merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }

    /**
     * Returns a BinaryFileResponse object with original or customized file name and disposition header.
     */
    public function file(SplFileInfo|string|Stringable $file, ?string $filename = null, string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        $response = new BinaryFileResponse($file instanceof SplFileInfo ? $file : ((string) $file));

        $filename ??= $response->getFile()->getFilename();
        $response->setContentDisposition($disposition, $filename);

        return $response;
    }
}
