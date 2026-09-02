<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Client\SoapClientFactory;
use App\Client\SoapClientFactoryInterface;
use App\Client\SoapClientInterface;
use App\Tests\Client\SoapClientFactoryStub;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ExpectationException;

class IONContext implements Context
{
    use AssertionTrait;
    use MinkAwareTrait;

    private ?SoapClientInterface $client = null;

    /**
     * @Then no requests have been sent to ION
     * @Then a total of :expectedTotal request(s) has been sent to ION
     */
    public function aTotalOfRequestsHaveBeenSent(int $expectedTotal = 0)
    {
        $container = $this->getClientContainer();
        /** @var SoapClientFactoryStub $soapClientFactory */
        $soapClientFactory = $container->get(SoapClientFactory::class);

        $total = 0;
        foreach ($soapClientFactory->getClients() as $client) {
            foreach ($client->getCalls() as $call) {
                $total += \count($call);
            }
        }

        $this->assertSame($expectedTotal, $total);
    }

    /**
     * @Given a :resource SOAP client has been created
     */
    public function aSOAPClientHasBeenCreated(string $resource)
    {
        $container = $this->getClientContainer();
        /** @var SoapClientFactoryStub $soapClientFactory */
        $soapClientFactory = $container->get(SoapClientFactoryInterface::class);

        $this->assertNotNull($this->client = $soapClientFactory->getClient($resource), \sprintf('No client found for resource %s', $resource));
    }

    /**
     * @Then this client has been called on the operation :operation with the following request:
     */
    public function thisClientHasBeenCalledOnOperationWithFollowingRequest(PyStringNode $body, string $operation)
    {
        $this->assertNotNull($this->client, 'No client found');

        $arguments = json_decode((string) $body, true, 512, \JSON_THROW_ON_ERROR);
        $callsReport = [];
        $calls = $this->client->getCalls();
        $this->assertNotEmpty($calls = ($calls[$operation] ?? []), \sprintf('No calls were made on operation %s', $operation));
        foreach ($calls as $call) {
            $callsReport[] = json_encode($call, \JSON_THROW_ON_ERROR | \JSON_PRETTY_PRINT);
            if ($arguments === $call) {
                return true;
            }
        }

        throw new ExpectationException(\sprintf("Can't find a SOAP call matching this request, found: \n %s)", implode("\n", $callsReport)), $this->getDriver());
    }
}
