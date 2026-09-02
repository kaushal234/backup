<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator
{
    private $charset = 'UTF-8';

    public function setCharset(string $charset)
    {
        $this->charset = $charset;
    }

    public function trans(?string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        return mb_convert_encoding(parent::trans($id, $parameters, $domain, $locale), $this->charset, 'UTF-8');
    }
}
