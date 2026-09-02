<?php

declare(strict_types=1);

namespace Shared\Provider\Common;

use Shared\Models\Common\Airport;
use Shared\Provider\AbstractProvider;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AirportProvider extends AbstractProvider
{
    public const AIRPORT_URL = '/airports';

    public function findByCode(string|int $code): Airport
    {
        $data =  $this->client->get(sprintf('%s?code=%s', self::AIRPORT_URL, $code));
        $data = $data['hydra:member'][0];

        if (null === $data) {
            throw new NotFoundHttpException(sprintf('Airport %s not found', $code));
        }

        $data['iri'] = $data['@id'];

        return $this->serializer->denormalize($data, Airport::class);
    }
}