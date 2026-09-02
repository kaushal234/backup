<?php

declare(strict_types=1);

namespace App\Postman\Factory;

use App\Postman\Resource\BodyInterface;
use App\Postman\Resource\JsonBody;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\SerializerInterface;

class JsonBodyFactory
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function create(array $fields): BodyInterface
    {
        $body = new JsonBody();
        $body->raw = $this->serializer->serialize($fields, 'json', [JsonEncode::OPTIONS => \JSON_PRETTY_PRINT]);

        return $body;
    }
}
