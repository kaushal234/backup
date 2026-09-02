<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension\Survey;

use ApiBundle\Hydra\HydraCollection;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sum', [$this, 'getSum']),
            new TwigFilter('count', [$this, 'getCount']),
            new TwigFilter('countUnique', [$this, 'getCountUnique']),
            new TwigFilter('countWithDateBetween', [$this, 'getCountByDateBetween']),
            new TwigFilter('people_info', [$this, 'getPeopleInfo']),
            new TwigFilter('to_encoding', [$this, 'convertEncoding']),
            new TwigFilter('htmlspecialchars', [$this, 'getHtmlSpecialChars']),
        ];
    }

    public function getHtmlSpecialChars($value): string
    {
        return htmlspecialchars((string) $value, \ENT_SUBSTITUTE, 'UTF-8');
    }

    public function convertEncoding($value, $enc = 'UTF-8'): string
    {
        return mb_convert_encoding((string) $value, $enc);
    }

    public function getPeopleInfo($people): string
    {
        return \sprintf('%s, %s - %s', $people['lastname'], $people['firstname'], $people['email']);
    }

    /**
     * @param string $property
     *
     * @return int
     */
    public function getSum(HydraCollection $collection, $property)
    {
        if (0 === $collection->count()) {
            return 0;
        }

        $sum = 0;
        foreach ($collection as $item) {
            $value = $item[$property];
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException(\sprintf('Property `%s` is not numeric!', $property));
            }
            $sum += $value;
        }

        return $sum;
    }

    public function getCount($collection, $property, $condition = true)
    {
        if ([] === $collection) {
            return 0;
        }

        if ($collection instanceof HydraCollection && 0 === $collection->count()) {
            return 0;
        }

        $count = 0;
        foreach ($collection as $item) {
            $val = $this->getValue($item, $property);
            if (!\is_array($val)) {
                $check = !\is_callable($condition) ? ($val === $condition) : $condition($val);
            } else {
                $check = $this->check($val, $condition);
            }
            if ($check) {
                ++$count;
            }
        }

        return $count;
    }

    public function getCountUnique($collection, $property)
    {
        if ([] === $collection) {
            return 0;
        }

        if ($collection instanceof HydraCollection && 0 === $collection->count()) {
            return 0;
        }
        $tmp = [];
        $count = 0;
        foreach ($collection as $item) {
            $val = $this->getValue($item, $property);
            if (!\in_array($val, $tmp, true)) {
                $tmp[] = $val;
                ++$count;
            }
        }

        return $count;
    }

    public function getCountByDateBetween($collection, $property, $startDate = null, $endDate = null)
    {
        if (!$startDate instanceof \DateTime) {
            $startDate = (new \DateTime())->modify('-1 month');
        }
        if (!$endDate instanceof \DateTime) {
            $endDate = new \DateTime();
        }
        $count = 0;
        foreach ($collection as $item) {
            $val = $this->getValue($item, $property);
            if (null === $val) {
                continue;
            }
            $propertyDate = new \DateTime($val);
            if ($propertyDate >= $startDate && $propertyDate <= $endDate) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName(): string
    {
        return 'surveys';
    }

    private function check(array $val, $condition = null)
    {
        switch ($condition) {
            case '!empty':
                $return = [] !== $val;
                break;
            case null:
            default:
                $return = [] === $val;
                break;
        }

        return $return;
    }

    private function getValue($item, $property)
    {
        if (false === mb_strpos((string) $property, '.')) {
            return $item[$property];
        }
        $explode = explode('.', (string) $property);
        $firstKey = array_shift($explode);
        if (\is_array($item[$firstKey])) {
            return $this->getValue($item[$firstKey], array_shift($explode));
        }

        return $item[$firstKey];
    }
}
