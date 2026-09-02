<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

class SanitationHelper
{
    /**
     * @param string $str        The string to clean
     * @param bool   $unescape   if set to true, all escaped characters will be removed
     * @param bool   $htmldecode if set to true, all HTML entities will be decoded to UTF8
     * @param bool   $multiline  if set to true, will add <br> tags for new lines
     * @param bool   $bypassTags if set to false, <br> tags won't be added in multiline strings already containing HTML
     */
    public function parse(string $str, bool $unescape = true, bool $htmldecode = true, bool $multiline = false, bool $bypassTags = true): string
    {
        if ($multiline) {
            $str = $this->multiline($str, $bypassTags);
        }
        if ($unescape) {
            $str = $this->unescape($str);
        }
        if ($htmldecode) {
            $str = $this->htmldecode($str);
        }

        return $str;
    }

    /**
     * @param string $str        The string to clean
     * @param bool   $bypassTags if set to false, the string containing HTML tags won't be formatted with new lines
     */
    public function multiline(string $str, $bypassTags = true): string
    {
        if ((false === mb_strpos($str, '<p') && false === mb_strpos($str, '<br')) || $bypassTags) {
            $str = str_replace(["\r\n", "\r", "\n"], '<br>', $str);
        }

        return $str;
    }

    /**
     * @param string $str The string to clean
     */
    public function unescape(string $str): string
    {
        return str_replace('\\', '', $str);
    }

    /**
     * @param string $str The string to trim and nullify
     */
    public function trimAndNullify(string $str): ?string
    {
        $string = mb_trim($str);

        return mb_strlen($string) > 0 ? $string : null;
    }

    /**
     * @param string $str The string to clean
     */
    public function htmldecode(string $str): string
    {
        return html_entity_decode($str);
    }

    /**
     * Trim an URL and add an http scheme if missing.
     *
     * @param string|null $url The string to clean
     */
    public function normalizeUrl(?string $url): ?string
    {
        if (null === $url) {
            return $url;
        }

        $url = mb_trim($url);
        if (0 === mb_strlen($url)) {
            return null;
        }

        $parsedUrl = parse_url($url);
        if (($parsedUrl['scheme'] ?? '') === '') {
            $url = 'http://'.mb_ltrim($url, '/');
        }

        return $url;
    }

    public function decodeChinese(string $str): string
    {
        $str = mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');

        return html_entity_decode(mb_convert_encoding($str, 'UTF-8', 'GBK'));
    }
}
