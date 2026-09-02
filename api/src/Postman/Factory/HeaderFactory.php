<?php

declare(strict_types=1);

namespace App\Postman\Factory;

use App\Postman\Resource\Header;

class HeaderFactory
{
    public function create(): Header
    {
        $header = new Header();
        $header->key = 'Authorization';
        $header->value = 'Bearer {{TLD-JWT}}';

        return $header;
    }
}
