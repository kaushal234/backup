<?php

declare(strict_types=1);

namespace App\Test\Helpers;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

trait AutocompleteTrait
{
    public static function assertTomSelectValue(Client $client, string $selectId, string $expectedValue): void
    {
        $value = $client->executeScript(
            \sprintf('return document.querySelector("select#%s").value', $selectId)
        );

        PantherTestCase::assertEquals($expectedValue, $value);

        $client->waitFor(\sprintf('select#%s.tomselected', $selectId));

        $tomSelectValue = $client->executeScript(
            \sprintf(
                'return document.querySelector("select#%s").tomselect?.getValue() ?? ""',
                $selectId
            )
        );

        PantherTestCase::assertEquals($expectedValue, $tomSelectValue);
    }

    public static function waitForInputValue(Client $client, string $selector, int $timeout = 15): string
    {
        $start = time();
        $value = '';

        while (time() - $start < $timeout) {
            $value = $client->executeScript(
                \sprintf('return document.querySelector("%s")?.value ?? ""', $selector)
            );
            if ('' !== $value) {
                break;
            }
            usleep(500000);
        }

        return $value;
    }
}
