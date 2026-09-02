<?php

declare(strict_types=1);

namespace ApiBundle;

use Symfony\Component\HttpClient\Exception\ClientException;

class ClientExceptionMapper
{
    public function mapToString(ClientException $e)
    {
        $body = json_decode($e->getResponse()->getContent(false), true);
        if (null === $body) {
            // The error is not in JSON, it can be anything (shouldn't be displayed to the final user)
            throw $e;
        }

        $message = null;
        if (!isset($body['violations'])) {
            $messages = [];
            if (isset($body['hydra:title'])) {
                $messages[] = $body['hydra:title'];
            }

            if (isset($body['hydra:description'])) {
                $messages[] = $body['hydra:description'];
            }

            return $messages ? implode("\n", $messages) : 'An unexpected error occurred';
        }

        $messages = [];
        foreach ($body['violations'] as $violation) {
            if (isset($violation['message'])) {
                $messages[] = $violation['message'];
            }
        }

        return $messages ? implode("\n", $messages) : 'An unexpected error occurred';
    }
}
