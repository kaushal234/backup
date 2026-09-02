<?php

declare(strict_types=1);

namespace App\Client;

interface SoapClientInterface
{
    public function __call($name, $arguments);

    public function __getLastRequest(): ?string;

    public function __getLastResponse(): ?string;

    public function __getLastRequestHeaders(): ?string;

    public function __getLastResponseHeaders(): ?string;

    public function getCalls(): array;

    public function disableSoapCalls();

    public function enableRecord();
}
