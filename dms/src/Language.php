<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Request;

class Language
{
    public const AVAILABLE_LANGUAGES = [
        'en' => [
            'name' => 'English',
            'language' => 'en_US.iso88591',
            'charset' => 'ISO-8859-1',
        ],
        'fr' => [
            'name' => 'French',
            'language' => 'fr_FR.iso88591',
            'charset' => 'ISO-8859-1',
        ],
        'zh' => [
            'name' => 'Chinese',
            'language' => 'zh_CN.gb2312',
            'charset' => 'GB2312',
        ],
    ];

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getLanguage()
    {
        $language = null;
        // Detect if other language requested
        if ($this->request->query->has('lang')) {
            if ($this->languageIso2Exist($this->request->query->get('lang'))) {
                $language = $this->request->query->get('lang');
            } else {
                $GLOBALS['_ERROR'][] = _('Language not supported');
            }
        }

        // Detect if language in session
        if (null === $language && !empty($GLOBALS['sess']['lang'])) {
            if ($this->languageIso2Exist($GLOBALS['sess']['lang'])) {
                $language = $GLOBALS['sess']['lang'];
            } else {
                // why this if copy paste from legacy
                $GLOBALS['_ERROR'][] = _('Language in session not supported');
            }
        }

        // Detect browser language
        if (null === $language && \array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            $language = mb_strtolower(mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
            if ($this->languageIso2Exist($language)) {
                $language = $language;
            } else {
                $GLOBALS['_NOTE'][] = _('Could not identify portail language, will default to English');
                $language = 'en';
            }
        }
        if (empty($language)) {
            $language = 'en';
        }

        putenv('LC_ALL='.mb_strtolower($language));
        setlocale(\LC_MESSAGES, self::AVAILABLE_LANGUAGES[$language]['language']);

        return $language;
    }

    private function languageIso2Exist(string $languageIso2)
    {
        return \array_key_exists($languageIso2, self::AVAILABLE_LANGUAGES);
    }
}
