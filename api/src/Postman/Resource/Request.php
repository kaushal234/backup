<?php

declare(strict_types=1);

namespace App\Postman\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Request
{
    public string $method;
    public Collection $header;
    public Url $url;
    public BodyInterface $body;

    public function __construct()
    {
        $this->header = new ArrayCollection();
    }

    public function addHeader(Header $header): void
    {
        $this->header->add($header);
    }
}
