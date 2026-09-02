<?php

declare(strict_types=1);

namespace App\Notifier;

use Symfony\Component\Mime\Email;

final class FromAddressFixer
{
    public static function fix(Email $email)
    {
        $validFroms = [];
        $replyTos = [];
        foreach ($email->getFrom() as $from) {
            [, $domain] = explode('@', $from->getAddress());
            if (\in_array($domain, ['air-rail.org', 'sageparts.com', 'freightquip.com', 'easymile.com'], true)) {
                $replyTos[] = $from;
                continue;
            }
            $validFroms[] = $from;
        }

        !empty($validFroms) ? $email->from(...$validFroms) : $email->from('noreply@tld-gse.com');

        $email->replyTo(...$replyTos);

        return $email;
    }
}
