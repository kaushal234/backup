<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context\Legacy\Helpers;

use Symfony\Component\VarDumper\Cloner\Data;

class QueryDecorator
{
    /**
     * Return a query with the parameters replaced.
     *
     * @param array|Data $parameters
     *
     * @return string
     *
     * This code is copy-pasted from the DoctrineBundle
     *
     * @see \Doctrine\Bundle\DoctrineBundle\Twig\DoctrineExtension
     */
    public static function replaceQueryParameters(string $query, $parameters): string
    {
        if ($parameters instanceof Data) {
            /** @var array $parameters */
            $parameters = $parameters->getValue(true);
        }

        $i = 0;
        if (!\array_key_exists(0, $parameters) && \array_key_exists(1, $parameters)) {
            $i = 1;
        }

        return preg_replace_callback(
            '#\?|((?<!:):[a-z0-9_]+)#i',
            static function ($matches) use ($parameters, &$i) {
                $key = mb_substr($matches[0], 1);

                if (!\array_key_exists($i, $parameters) && (!$key || !\array_key_exists($key, $parameters))) {
                    return $matches[0];
                }

                $value = $parameters[$i] ?? $parameters[$key] ?? null;
                $result = self::escapeFunction($value);
                ++$i;

                return $result;
            },
            $query
        );
    }

    /**
     * Escape parameters of a SQL query
     * DON'T USE THIS FUNCTION OUTSIDE ITS INTENDED SCOPE.
     *
     * @internal
     *
     * @return string|int
     *
     * This code is copy-pasted from the DoctrineBundle
     *
     * @see \Doctrine\Bundle\DoctrineBundle\Twig\DoctrineExtension
     */
    public static function escapeFunction($parameter)
    {
        $result = $parameter;

        switch (true) {
            // Check if result is non-unicode string using PCRE_UTF8 modifier
            case \is_string($result) && !preg_match('//u', $result):
                $result = '0x'.mb_strtoupper(bin2hex($result));
                break;
            case \is_string($result):
                $result = "'".addslashes($result)."'";
                break;
            case \is_array($result):
                foreach ($result as &$value) {
                    $value = self::escapeFunction($value);
                }

                $result = implode(', ', $result);
                break;
            case \is_object($result):
                $result = addslashes((string) $result);
                break;
            case null === $result:
                $result = 'NULL';
                break;
            case \is_bool($result):
                $result = $result ? '1' : '0';
                break;
        }

        return $result;
    }
}
