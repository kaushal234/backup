<?php

declare(strict_types=1);

namespace App\AI\Service;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final readonly class AiJsonResponseParser
{
    /**
     * @return array<string, mixed>
     */
    public function parse(string $raw): array
    {
        $json = mb_trim($raw);
        $json = preg_replace('/\A```(?:json)?\s*|\s*```\z/', '', $json) ?? $json;

        $parsed = json_decode($json, true);
        if (\JSON_ERROR_NONE !== json_last_error() || !\is_array($parsed)) {
            throw new ServiceUnavailableHttpException(null, 'Invalid AI JSON response: '.json_last_error_msg());
        }

        return $parsed;
    }
}
