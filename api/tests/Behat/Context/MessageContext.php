<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Mink\Exception\ExpectationException;
use Symfony\Component\Messenger\Envelope;

class MessageContext implements Context
{
    use AssertionTrait;
    use MinkAwareTrait;

    /**
     * @Then a message of class :class should have been sent in the bus
     */
    public function messageHasBeenSentInTheBus(string $class)
    {
        $transport = $this->getClientContainer()->get('messenger.transport.async');

        /** @var Envelope $envelope */
        foreach ($transport->get() as $envelope) {
            $message = $envelope->getMessage();
            if ($message instanceof $class) {
                return true;
            }
        }

        throw new ExpectationException(\sprintf("can't find a message of class %s", $class), $this->getDriver());
    }

    /**
     * @Then no message of class :class should have been sent in the bus
     */
    public function messageHasNotBeenSentInTheBus(string $class)
    {
        $transport = $this->getClientContainer()->get('messenger.transport.async');

        /** @var Envelope $envelope */
        foreach ($transport->get() as $envelope) {
            $message = $envelope->getMessage();
            $this->assertInstanceOf($class, $message, \sprintf('Found a message of class %s', $class));
        }
    }
}
