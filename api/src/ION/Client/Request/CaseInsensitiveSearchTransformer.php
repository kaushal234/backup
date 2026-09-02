<?php

declare(strict_types=1);

namespace App\ION\Client\Request;

use Symfony\Component\String\Slugger\SluggerInterface;

class CaseInsensitiveSearchTransformer
{
    private readonly SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function transform(string $string): string
    {
        $transformedString = '';
        foreach (mb_str_split($string) as $character) {
            if (is_numeric($character)) {
                $transformedString .= $character;
                continue;
            }

            $characterVersions = [];

            $characterVersions[] = $character;
            $characterVersions[] = mb_strtoupper($character);
            $characterVersions[] = mb_strtolower($character);

            if (1 === mb_strlen($slugged = (string) $this->slugger->slug($character))) {
                $characterVersions[] = $slugged;
                $characterVersions[] = mb_strtoupper($slugged);
                $characterVersions[] = mb_strtolower($slugged);
            }

            $characterVersions = array_unique($characterVersions);

            if (1 === \count($characterVersions)) {
                $transformedString .= $this->escapeSpecialCharacters($character);
                continue;
            }

            $characterVersions = array_map($this->escapeSpecialCharacters(...), $characterVersions);

            $transformedString .= \sprintf('[%s]', implode('', $characterVersions));
        }

        return $transformedString;
    }

    public function escapeSpecialCharacters(string $string): string
    {
        return preg_quote(str_replace(['[', ']'], '', $string), '/');
    }
}
