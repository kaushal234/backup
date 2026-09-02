<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Formatter;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

class DateRangeActiveFilterFormatterTest extends TestCase
{
    public function getTranslator(): TranslatorInterface
    {
        return new class implements TranslatorInterface {
            use TranslatorTrait;
        };
    }

    /**
     * @dataProvider providerTemplate
     */
    public function test(array $values, string $expected): void
    {
        $filterData = new FilterData($values);
        $formatter = new DateRangeActiveFilterFormatter();
        $result = $formatter($filterData);
        if ($result instanceof TranslatableMessage) {
            $result = $result->trans($this->getTranslator());
        }

        $this->assertSame($expected, $result);
    }

    public function providerTemplate(): \Generator
    {
        yield [['from' => '2025-01-01T08:00:00Z', 'to' => null], 'After 2025-01-01'];
        yield [['from' => null, 'to' => '2025-01-01T08:00:00Z'], 'Before 2025-01-01'];
        yield [['from' => '2025-01-01T08:00:00Z', 'to' => '2025-01-10T08:00:00Z'], '2025-01-01 - 2025-01-10'];
        yield [['from' => new \DateTime('2025-01-01'), 'to' => new \DateTime('2025-01-10')], '2025-01-01 - 2025-01-10'];
    }
}
