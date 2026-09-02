<?php

declare(strict_types=1);

namespace AppBundle\DataPersister;

use ApiBundle\Client;

class CommentPersister
{
    public const COMMENT_URL = '/comments';

    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function save(?string $message, string $resource, ?string $discriminator = null)
    {
        return $this->client->save(self::COMMENT_URL, [
            'message' => $message,
            'resource' => $resource,
            'discriminator' => $discriminator,
        ]);
    }
}
