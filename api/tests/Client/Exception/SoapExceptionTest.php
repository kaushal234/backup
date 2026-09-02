<?php

declare(strict_types=1);

namespace App\Tests\Client\Exception;

use App\Client\Exception\SoapException;
use PHPUnit\Framework\TestCase;

class SoapExceptionTest extends TestCase
{
    public function testThatSoapFaultMessagesAreCorrectlyHandled()
    {
        $detail = (object) [
            'Result' => (object) [
                'messageText' => "I'm gonna be iron like a lion in Zion",
                'MessageDetails' => (object) [
                    'Message' => (object) [
                        (object) ['messageType' => 'troisieme', 'messageText' => 'paul'],
                        (object) ['messageType' => 'Error', 'messageText' => 'Objecto esta no fouñado.'],
                        (object) ['messageType' => 'Coucou', 'messageText' => 'Object not found.'],
                    ],
                ],
            ],
        ];

        $exception = SoapException::createFromSoapFault('IonLionZion', new \SoapFault('quantum', 'error', 'Scott Bakula', $detail));

        $this->assertSame("I'm gonna be iron like a lion in Zion", $exception->getMessage());

        $messages = $exception->getMessages();

        $this->assertCount(4, $messages);
        $this->assertTrue($messages[0]->isError());
        $this->assertFalse($messages[0]->isNotFound());
        $this->assertFalse($messages[1]->isError());
        $this->assertFalse($messages[1]->isNotFound());
        $this->assertTrue($messages[2]->isError());
        $this->assertSame('Error', $messages[2]->type);
        $this->assertFalse($messages[2]->isNotFound());
        $this->assertFalse($messages[3]->isError());
        $this->assertTrue($messages[3]->isNotFound());
        $this->assertSame('Object not found.', $messages[3]->text);
    }
}
