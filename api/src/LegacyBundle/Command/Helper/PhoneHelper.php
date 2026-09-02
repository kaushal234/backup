<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Helper;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneHelper
{
    private readonly PhoneNumberUtil $phoneNumberUtil;

    /**
     * PhoneHelper constructor.
     */
    public function __construct(PhoneNumberUtil $phoneNumberUtil)
    {
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    /**
     * @param string $str
     */
    public function parsePhone($str, array $regions = [])
    {
        $str = str_replace(['/', '-', 'DO NOT DELETE', 'NA', '?', 'unknown', '*******'], '', mb_trim($str));
        $str = str_replace([' (Gen', ' (Sales and Service)'], '', $str);
        $str = current(explode(', ', $str));

        if (empty($str)) {
            return $str;
        }

        $regions[] = PhoneNumberUtil::UNKNOWN_REGION;
        $regions[] = 'FR';

        $number = null;
        foreach ($regions as $region) {
            try {
                $number = $this->phoneNumberUtil->parse($str, $region);
                if ($this->phoneNumberUtil->isValidNumber($number)) {
                    break;
                }
                $number = null;
            } catch (\Exception $exception) {
            }
        }
        if (!$number instanceof PhoneNumber) {
            foreach ($regions as $region) {
                try {
                    $number = $this->phoneNumberUtil->parse('+'.$str, $region);
                    if ($this->phoneNumberUtil->isValidNumber($number)) {
                        break;
                    }
                    $number = null;
                } catch (\Exception $exception) {
                }
            }
        }
        if (!$number instanceof PhoneNumber) {
            return $str;
        }

        return $this->phoneNumberUtil->format($number, PhoneNumberFormat::INTERNATIONAL);
    }
}
