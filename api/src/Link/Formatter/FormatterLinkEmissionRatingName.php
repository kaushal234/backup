<?php

declare(strict_types=1);

namespace App\Link\Formatter;

class FormatterLinkEmissionRatingName implements FormatterLinkInterface
{
    /** @var string */
    public const FUEL = 'FUEL';

    /** @var string */
    public const BATTERY_LITHIUM_IBS = 'BATTERY_LITHIUM_IBS';

    /** @var string */
    public const HYBRID = 'HYBRID';

    /** @var string */
    public const BATTERY_LEAD_ACID = 'BATTERY_LEAD_ACID';

    /** @var string */
    public const BATTERY_LITHIUM_OTHER = 'BATTERY_LITHIUM_OTHER';

    public function __invoke($value, array $options, string $field)
    {
        return [$field => $this->formatValue($value)];
    }

    public static function formatValue(?string $value): ?string
    {
        switch (true) {
            case str_contains($value, 'CN GB'):
            case str_contains($value, 'Stage'):
            case str_contains($value, 'Tier'):
            case 'LPG-CNG' === $value:
            case 'Gas' === $value:
                $formattedValue = self::FUEL;
                break;
            case 'iBS' === $value:
                $formattedValue = self::BATTERY_LITHIUM_IBS;
                break;
            case 'iHs' === $value:
            case 'ipHS + iBS' === $value:
                $formattedValue = self::HYBRID;
                break;
            case 'Lead-Acid' === $value:
            case 'Customer Lead-acid' === $value:
                $formattedValue = self::BATTERY_LEAD_ACID;
                break;
            case 'TLD Li-ion (not iBS)' === $value:
                $formattedValue = self::BATTERY_LITHIUM_OTHER;
                break;
            default:
                $formattedValue = null;
        }

        return $formattedValue;
    }
}
