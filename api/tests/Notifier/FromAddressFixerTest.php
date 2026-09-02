<?php

declare(strict_types=1);

namespace App\Tests\Notifier;

use App\Notifier\FromAddressFixer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class FromAddressFixerTest extends TestCase
{
    /**
     * @dataProvider provideEmails
     */
    public function testNonManagedEmailAddressesAreFilteredOut(Email $email, array $expectedFroms, array $expectedReplyTos)
    {
        $email = FromAddressFixer::fix($email);

        $froms = array_map(static fn (Address $address) => $address->getAddress(), $email->getFrom());
        $replyTos = array_map(static fn (Address $address) => $address->getAddress(), $email->getReplyTo());

        self::assertSame($expectedFroms, $froms);
        self::assertSame($expectedReplyTos, $replyTos);
    }

    public function provideEmails()
    {
        yield 'Valid email' => [(new Email())->from('tempor@rypatch.rocks'), ['tempor@rypatch.rocks'], []];
        yield 'Valid email from TLD' => [(new Email())->from('someone@tld-america.com'), ['someone@tld-america.com'], []];
        yield 'Someone from Air rail' => [(new Email())->from('tchoutchou@air-rail.org'), ['noreply@tld-gse.com'], ['tchoutchou@air-rail.org']];
        yield 'Someone from Sageparts' => [(new Email())->from('onreparetout@sageparts.com'), ['noreply@tld-gse.com'], ['onreparetout@sageparts.com']];
        yield 'Someone from Freightquip' => [(new Email())->from('equipe@freightquip.com'), ['noreply@tld-gse.com'], ['equipe@freightquip.com']];
        yield 'Someone from Easymile' => [(new Email())->from('onroulefacile@easymile.com'), ['noreply@tld-gse.com'], ['onroulefacile@easymile.com']];
    }
}
