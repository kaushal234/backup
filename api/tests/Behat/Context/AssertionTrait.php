<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Mink\Exception\ExpectationException;

trait AssertionTrait
{
    use MinkAwareTrait;

    protected function not(callable $callable, string $message)
    {
        try {
            $callable();
        } catch (\Exception $e) {
            return;
        }

        throw new ExpectationException($message, $this->getDriver());
    }

    protected function assert(bool $test, string $message)
    {
        if (!$test) {
            throw new ExpectationException($message, $this->getDriver());
        }
    }

    protected function assertContains(string $expected, string $actual, ?string $message = null)
    {
        $regex = '/'.preg_quote($expected, '/').'/ui';

        $this->assert(
            preg_match($regex, $actual) > 0,
            $message ?? "The string '$expected' was not found."
        );
    }

    protected function assertNotContains(string $expected, string $actual, ?string $message = null)
    {
        $regex = '/'.preg_quote($expected, '/').'/ui';

        $this->assert(
            preg_match($regex, $actual) > 0,
            $message ?? "The string '$expected' was not found."
        );
    }

    protected function assertCount(int $expected, array $elements, ?string $message = null)
    {
        $this->assert($expected === \count($elements), $message ?? \sprintf('%d elements found, but should be %d.', \count($elements), $expected));
    }

    protected function assertEmpty(array $elements, ?string $message = null)
    {
        $this->assert([] === $elements, $message ?? 'The element was not empty');
    }

    protected function assertNotEmpty(array $elements, ?string $message = null)
    {
        $this->assert([] !== $elements, $message ?? 'The element was empty');
    }

    protected function assertSame($expected, $actual, ?string $message = null)
    {
        $this->assert($expected === $actual, $message ?? "The element '$actual' is not equal to '$expected'");
    }

    protected function assertArrayHasKey(string $key, array $array, ?string $message = null)
    {
        $this->assert(isset($array[$key]), $message ?? "The array has no key '$key'");
    }

    protected function assertArrayContains($value, array $array, ?string $message = null)
    {
        $this->assert(\in_array($value, $array, true), $message ?? "The array doesn\'t contain the element");
    }

    protected function assertArrayDoesntContain($value, array $array, ?string $message = null)
    {
        $this->assert(!\in_array($value, $array, true), $message ?? 'The array contains the element');
    }

    protected function assertTrue(bool $value, ?string $message = 'The value is false')
    {
        $this->assert($value, $message);
    }

    protected function assertFalse(bool $value, ?string $message = 'The value is true')
    {
        $this->assert(!$value, $message);
    }

    protected function assertNull($value, ?string $message = 'The value is null')
    {
        $this->assert(null === $value, $message);
    }

    protected function assertNotNull($value, ?string $message = 'The value is null')
    {
        $this->assert(null !== $value, $message);
    }

    protected function assertInstanceOf(string $expected, object $value, ?string $message = 'The object is not an instance of the requested class')
    {
        $this->assert($value instanceof $expected, $message);
    }
}
