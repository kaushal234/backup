<?php

declare(strict_types=1);

namespace App\Http;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ModuloClient
{
    final public const GET_CONTACT_FORM_SUBMITTED = 'getContactFormSubmitted';
    final public const CLOSE_CONTACT_FORM_ENTRIES = 'closeContactFormEntries';

    public function __construct(
        protected HttpClientInterface $moduloClient,
        private readonly LoggerInterface $moduloRequestLogger
    ) {
    }

    public function getContactFormSubmitted(): array
    {
        try {
            $response = $this->moduloClient->request(
                Request::METHOD_GET,
                self::GET_CONTACT_FORM_SUBMITTED
            );

            return json_decode($response->getContent(), true);
        } catch (ExceptionInterface $exception) {
            $this->logModuloError(self::GET_CONTACT_FORM_SUBMITTED, $exception->getMessage());

            return [];
        }
    }

    public function closeContactFormEntries(array $formEntriesIds): void
    {
        if (empty($formEntriesIds)) {
            $this->moduloRequestLogger->info('No Pending Extranet User Access request to close');

            return;
        }

        try {
            $this->moduloClient->request(
                Request::METHOD_POST,
                self::CLOSE_CONTACT_FORM_ENTRIES,
                [
                    'body' => json_encode(['contactFormIDList' => $formEntriesIds]),
                ],
            );
            $this->moduloRequestLogger->info(\sprintf('%s Pending Extranet User Access request(s) closed', \count($formEntriesIds)));

            return;
        } catch (ExceptionInterface $exception) {
            $this->logModuloError(self::CLOSE_CONTACT_FORM_ENTRIES, $exception->getMessage());

            return;
        }
    }

    private function logModuloError(string $endPoint, string $error): void
    {
        $this->moduloRequestLogger->debug('{time} : EndPoint {endPoint} error: {error}', [
            'time' => date('Y-m-d H:i:s'),
            'endPoint' => $endPoint,
            'error' => $error,
        ]);
    }
}
