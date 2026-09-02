<?php

declare(strict_types=1);

namespace App\Http;

use App\AI\Factory\AILogFactory;
use App\Dto\DeeplTranslator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DeeplClient
{
    final public const ORIGINAL_COMMENT = 'Original comment';

    public function __construct(
        protected HttpClientInterface $deeplClient,
        private readonly LoggerInterface $deeplRequestLogger,
        private readonly AILogFactory $factory,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getTranslation(
        string $baseText,
        $targetLang = DeeplTranslator::DEFAULT_LANGUAGE_CODE,
        ?string $class = null,
        ?int $itemId = null,
        ?bool $returnOnlyTranslatedMessage = false,
        ?string $formality = DeeplTranslator::DEFAULT_FORMALITY,
        bool $createLog = true,
    ): string {
        try {
            $body = [
                'formality' => $formality,
                'text' => $baseText,
                'target_lang' => $targetLang,
            ];

            $aiRequest = $this->factory->createRequest('translate', $body);

            $response = $this->deeplClient->request(
                Request::METHOD_POST,
                'translate',
                [
                    'body' => $body,
                ],
            );
            if (Response::HTTP_OK !== $response->getStatusCode()) {
                $this->logTranslationError($response->getContent(), $class, (string) $itemId);

                return $baseText;
            }
            $translation = $response->toArray()['translations'][0]['text'];

            $currentRequest = $this->requestStack->getCurrentRequest();
            $shouldLog = $createLog && (null === $currentRequest || $currentRequest->query->getBoolean('createLog', true));

            if ($shouldLog) {
                $this->factory->createLog($aiRequest, $translation);
            }

            if ($returnOnlyTranslatedMessage) {
                return $translation;
            }

            return \sprintf('%s

            %s: %s', $translation, self::ORIGINAL_COMMENT, $baseText);
        } catch (
            ExceptionInterface $exception) {
                $this->logTranslationError($exception->getMessage(), $class, (string) $itemId);

                return $baseText;
            }
    }

    /**
     * POST document — upload a document to DeepL.
     *
     * @see https://www.deepl.com/docs-api/document/
     */
    public function uploadDocument(UploadedFile $uploaded, string $targetLang): array
    {
        $form = new FormDataPart([
            'file' => DataPart::fromPath(
                $uploaded->getPathname(),
                $uploaded->getClientOriginalName() ?: ($uploaded->getFilename() ?: 'upload'),
                $uploaded->getClientMimeType() ?: ($uploaded->getMimeType() ?? 'application/octet-stream')
            ),
            'target_lang' => $targetLang,
        ]);

        $airequest = $this->factory->createRequest('document', [], $uploaded);

        $response = $this->deeplClient->request(
            Request::METHOD_POST,
            'document',
            [
                'headers' => $form->getPreparedHeaders()->toArray(),
                'body' => $form->toIterable(),
            ],
        );

        if (200 !== $response->getStatusCode()) {
            $this->logDocumentTranslationError($response);
            throw new BadRequestHttpException('DeepL upload failed. Please try again later.');
        }

        $this->factory->createLog($airequest, $response->getContent(false));

        return $response->toArray();
    }

    public function getDocumentStatus(string $documentId, string $documentKey): array
    {
        $response = $this->deeplClient->request(
            Request::METHOD_GET,
            \sprintf('document/%s', urlencode($documentId)),
            ['query' => ['document_key' => $documentKey]]
        );

        if (200 !== $response->getStatusCode()) {
            $this->logDocumentTranslationError($response);
            throw new BadRequestHttpException('DeepL is temporarily unavailable. Please try again later.');
        }

        return $response->toArray();
    }

    public function downloadDocument(string $documentId, string $documentKey): StreamedResponse
    {
        $response = $this->deeplClient->request(
            Request::METHOD_POST,
            \sprintf('document/%s/result', urlencode($documentId)),
            ['query' => ['document_key' => $documentKey]]
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->logDocumentTranslationError($response);
            if (404 === $response->getStatusCode()) {
                throw new BadRequestHttpException(\sprintf('The translated file is no longer available (expired or already downloaded) (Deepl HTTP %d).', $response->getStatusCode()));
            }
            throw new BadRequestHttpException(\sprintf('DeepL is temporarily unavailable. Please try again later. (HTTP %d)', $response->getStatusCode()));
        }

        $headers = $response->getHeaders(false);

        return new StreamedResponse(function () use ($response) {
            foreach ($this->deeplClient->stream($response) as $chunk) {
                if ($chunk->isTimeout()) {
                    continue;
                }
                $content = $chunk->getContent();
                if ('' !== $content) {
                    echo $content;
                    flush();
                }
            }
        }, Response::HTTP_OK, [
            'Content-Type' => $headers['content-type'][0] ?? 'application/octet-stream',
            'Content-Length' => $headers['content-length'][0] ?? null,
            'Content-Disposition' => $headers['content-disposition'][0] ?? null,
        ]);
    }

    private function logTranslationError(string $error, ?string $class = '', ?string $itemId = ''): void
    {
        $this->deeplRequestLogger->debug('{time} : Could not translate the comment for {item} {id}. Error : {error}', [
            'time' => date('Y-m-d H:i:s'),
            'item' => $class,
            'id' => $itemId,
            'error' => $error,
        ]);
    }

    private function logDocumentTranslationError(ResponseInterface $response): void
    {
        $this->deeplRequestLogger->error('DeepL status non-200', [
            'http_code' => $response->getStatusCode(),
            'error_body' => $response->getContent(false),
            'content_type' => $response->getHeaders(false)['content-type'][0] ?? null,
        ]);
    }
}
