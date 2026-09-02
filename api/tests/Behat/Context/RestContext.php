<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Element\DocumentElement;
use Behatch\Context\RestContext as BaseRestContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RestContext implements Context
{
    use AssertionTrait;
    private BaseRestContext $restContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        /** @var BaseRestContext $context */
        $context = $environment->getContext(BaseRestContext::class);
        $this->restContext = $context;
    }

    /**
     * Sends a HTTP request with a body.
     *
     * @Given I send a :method request to :url with the body :filename
     */
    public function iSendARequestToWithTheBody($method, $url, $filename)
    {
        $this->assertTrue(is_file($filename), "The body file doesn't exist");

        $body = new PyStringNode([file_get_contents($filename)], 0);
        $this->restContext->iSendARequestTo($method, $url, $body);
    }

    /**
     * Sends a HTTP request with a body.
     *
     * @Given I send a :method request to :url with the body :filename and replace :property by the date :dateValue
     */
    public function iSendARequestToWithTheBodyAndReplaceByTheDate($method, $url, $filename, $property, $dateValue = 'now')
    {
        $this->assertTrue(is_file($filename), "The body file doesn't exist");

        $json = json_decode(file_get_contents($filename), true);

        $this->assertArrayHasKey($property, $json, \sprintf('The property %s is not present in the given file', $property));

        $json[$property] = (new \DateTime($dateValue))->format('Y-m-d');

        $body = new PyStringNode([json_encode($json, \JSON_UNESCAPED_SLASHES)], 0);
        $this->restContext->iSendARequestTo($method, $url, $body);
    }

    /**
     * @Given I send a :method request to :url with body and replace :property by the date :dateValue:
     */
    public function iSendARequestToWithBodyAndReplaceByTheDate($method, $url, $property, $dateValue, PyStringNode $body)
    {
        $json = json_decode($body->getRaw(), true);

        $this->assertArrayHasKey($property, $json, \sprintf('The property %s is not present in the given file', $property));

        $json[$property] = (new \DateTime($dateValue))->format('Y-m-d');

        $body = new PyStringNode([json_encode($json, \JSON_UNESCAPED_SLASHES)], 0);
        $this->restContext->iSendARequestTo($method, $url, $body);
    }

    /**
     * Sends a HTTP request with a file.
     *
     * @Given I send a :method request to :url with file :key :file
     */
    public function iSendARequestToWithFile($method, $url, $key, $file)
    {
        $filePath = $this->restContext->getMinkParameter('files_path').'/'.$file;
        $this->restContext->iSendARequestTo(
            $method, $url, null, [$key => new UploadedFile($filePath, $file)]
        );
    }

    /**
     * Sends a HTTP request with a date concatenated to it.
     *
     * @Given I send a :method request to :url with date :dateValue
     */
    public function iSendARequestToWithDate($method, $url, $dateValue, ?PyStringNode $body = null, $files = [])
    {
        $date = (new \DateTime($dateValue))->format('Y-m-d');

        $this->restContext->iSendARequestTo($method, $url.$date, $body, $files);
    }

    /**
     * @When I send a :method request to the first element of the collection :url with body:
     */
    public function iSendARequestToTheFirstElementOfTheCollection(string $method, string $url, PyStringNode $body)
    {
        $client = $this->getClient();
        $headers = [];
        foreach (['HTTP_AUTHORIZATION' => 'Authorization', 'HTTP_ACCEPT' => 'Accept', 'CONTENT_TYPE' => 'Content-Type'] as $clientHeader => $httpHeader) {
            if (null !== ($headerValue = $client->getServerParameter($clientHeader, null))) {
                $headers[$httpHeader] = $headerValue;
            }
        }

        /** @var DocumentElement $response */
        $response = $this->restContext->iSendARequestTo('GET', $url);
        $collection = json_decode($response->getContent(), true)['hydra:member'] ?? [];

        foreach ($headers as $header => $value) {
            $this->restContext->iAddHeaderEqualTo($header, $value);
        }

        $this->assertNotEmpty($collection, \sprintf('The call to %s returned an empty collection', $url));

        $this->restContext->iSendARequestTo($method, $collection[0]['@id'], $body);
    }
}
