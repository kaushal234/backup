<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use App\Entity\AddressWithCountry;

class AddressHelper
{
    /**
     * @param string $str
     */
    public function parseAddress($str)
    {
        $str = str_replace(
            \chr(150),
            '-',
            mb_trim(stripslashes($str))
        );

        $splitResult = preg_split("/[\r\n]+/", preg_replace('#(\s+\\-\s+|\s*[,;]\s+|[,]+)#', "\n", $str));

        if (false === $splitResult) {
            return [];
        }

        /** @var string[] $lines */
        $lines = $splitResult;

        array_walk($lines, fn ($line) => $this->trimLine($line));
        $parsers = $this->getParsers();
        foreach ($parsers as $parser) {
            if (null !== $address = $parser($lines)) {
                return $address;
            }
        }
    }

    private function trimLine($line)
    {
        return preg_replace('#^[\s\-\–]+#', '', preg_replace('#[\s\-\–]+$#', '', (string) $line));
    }

    private function getParsers()
    {
        $frParser = function ($lines) {
            if ('france' !== mb_strtolower((string) array_pop($lines))) {
                return;
            }
            $lastLine = array_pop($lines);
            if (null === $lastLine) {
                return;
            }
            preg_match('#(.*)[\s,\-]*(\d{5})[\s,\-]+(.*)#', $lastLine, $matches);
            if (\count($matches) < 4) {
                return;
            }
            if ('' !== $matches[1] && '0' !== $matches[1]) {
                $lines[] = $matches[1];
            }

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setPostalCode($matches[2])
                ->setCity($matches[3])
                ->setCountry('FR')
            ;
        };
        $ueParser = function ($lines) {
            if ('united arab emirates' !== mb_strtolower((string) array_pop($lines))) {
                return;
            }

            $lastLine = array_pop($lines);
            if (null === $lastLine) {
                return;
            }
            if ('dubai airport free zone' === mb_strtolower($lastLine)) {
                $postalCode = null;
                $city = $lastLine;
            } else {
                preg_match('#(.*)[\s,\-]+(\d{5})[\s,\-]+(.*)#', $lastLine, $matches);
                if (\count($matches) < 4) {
                    return;
                }
                if ('' !== $matches[1] && '0' !== $matches[1]) {
                    $lines[] = $matches[1];
                }

                $postalCode = $matches[2];
                $city = $matches[3];
            }

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setPostalCode($postalCode)
                ->setCity($city)
                ->setCountry('AE')
            ;
        };
        $usParser = function ($lines) {
            if ('united states of america' !== mb_strtolower((string) array_pop($lines))) {
                return;
            }

            $cityState = array_pop($lines);
            if (null === $cityState) {
                return;
            }
            $cityState = array_pop($lines).' '.$cityState;
            preg_match('#(.*)[\s,\-]+(\d{5})[\s,\-]+(.*)#', $cityState, $matches);
            if (\count($matches) < 4) {
                return;
            }

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setPostalCode($matches[2])
                ->setCity($matches[1])
                ->setState($matches[3])
                ->setCountry('US')
            ;
        };
        $hkParser = function ($lines) {
            if ('hong kong' !== mb_strtolower((string) array_pop($lines))) {
                return;
            }

            $city = array_pop($lines);

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setCity($this->trimLine($city))
                ->setCountry('HK')
            ;
        };
        $cnParser = function ($lines) {
            if ('china' !== mb_strtolower((string) array_pop($lines))) {
                return;
            }

            $lastLine = array_pop($lines);
            if (null === $lastLine) {
                return;
            }

            preg_match('#(.*)[\s,\-]+(\d{5})#', $lastLine, $matches);
            if (\count($matches) < 3) {
                $postalCode = null;
                $city = $lastLine;
            } else {
                $postalCode = $matches[2];
                $city = $matches[1];
            }

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setPostalCode($postalCode)
                ->setCity($city)
                ->setCountry('CN')
            ;
        };
        $qtParser = function ($lines) {
            $country = array_pop($lines);
            if (null === $country) {
                return;
            }
            if (!preg_match('#(?:canada|qt)[\.\s]+([\w\d]{3} [\w\d]{3})#i', $country, $matches)) {
                return;
            }
            $postalCode = $matches[1];
            $lastLine = array_pop($lines);
            $words = explode(' ', $lastLine);
            $city = array_pop($words);
            $lines[] = implode(' ', $words);

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setPostalCode($postalCode)
                ->setCity($this->trimLine($city))
                ->setCountry('CA')
            ;
        };
        $sgParser = function ($lines) {
            $country = array_pop($lines);
            if (null === $country) {
                return;
            }
            preg_match('#^Singapore[\s,\-]+(\d{6})$#i', $country, $matches);
            if (\count($matches) < 2) {
                return;
            }

            $postalCode = $matches[1];
            $city = array_pop($lines);

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setPostalCode($postalCode)
                ->setCity($this->trimLine($city))
                ->setCountry('SG')
            ;
        };
        $jpParser = function ($lines) {
            $country = array_pop($lines);
            if (null === $country) {
                return;
            }
            preg_match('#^(.*)[\s,\-]+(?:japan|\(japan\))$#i', $country, $matches);
            if (\count($matches) < 2) {
                return;
            }
            $city = $matches[1];

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setCity($this->trimLine($city))
                ->setCountry('JP')
            ;
        };
        $ucCTParser = function ($lines) {
            $country = array_pop($lines);
            if (null === $country) {
                return;
            }
            preg_match('#^CT[\s]+(\d{5})$#i', $country, $matches);
            if (\count($matches) < 2) {
                return;
            }

            $postalCode = $matches[1];
            $state = 'Connecticut';
            $city = array_pop($lines);

            return (new AddressWithCountry())
                ->setStreet1($this->trimLine(implode("\n", $lines)))
                ->setPostalCode($postalCode)
                ->setCity($this->trimLine($city))
                ->setState($this->trimLine($state))
                ->setCountry('US')
            ;
        };

        return [
            $frParser,
            $ueParser,
            $usParser,
            $hkParser,
            $cnParser,
            $qtParser,
            $sgParser,
            $jpParser,
            $ucCTParser,
        ];
    }
}
