<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\JsonContext as BaseJsonContext;
use Behatch\HttpCall\HttpCallResultPool;
use Behatch\Json\Json;
use Behatch\Json\JsonInspector;
use Symfony\Component\HttpFoundation\Response;

class JsonContext implements Context
{
    use AssertionTrait;

    private HttpCallResultPool $httpCallResultPool;
    private JsonInspector $inspector;
    private BaseJsonContext $jsonContext;
    private MinkContext $minkContext;

    /**
     * @BeforeScenario
     */
    public function beforeScenario(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        /** @var BaseJsonContext $context */
        $context = $environment->getContext(BaseJsonContext::class);
        $this->jsonContext = $context;

        /** @var MinkContext $context */
        $context = $environment->getContext(MinkContext::class);
        $this->minkContext = $context;

        $reflectionClass = new \ReflectionClass(BaseJsonContext::class);

        $httpCallResultPoolProperty = $reflectionClass->getProperty('httpCallResultPool');
        $inspectorProperty = $reflectionClass->getProperty('inspector');

        $httpCallResultPoolProperty->setAccessible(true);
        $inspectorProperty->setAccessible(true);

        $this->httpCallResultPool = $httpCallResultPoolProperty->getValue($this->jsonContext);
        $this->inspector = $inspectorProperty->getValue($this->jsonContext);
    }

    /**
     * Checks, that given JSON array node contains a given element.
     *
     * @Then one JSON array element at node :node should contain :element in property :key
     */
    public function OneJsonArrayElementAtNodeShouldContainInProperty($node, $element, $key)
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        $results = [];
        foreach ($actual as $index => $subNode) {
            if (property_exists($subNode, $key)) {
                $results[] = $subNode->{$key};
            }
        }
        $this->assertArrayContains($element, $results, \sprintf("The property '%s' was not found in any node  '%s'", $key, $element));
    }

    /**
     * @Then the response should be an error stating :errorDescription
     * @Then the response should be an error stating :errorDescription with status code :statusCode
     */
    public function theResponseShouldBeAnErrorStating($errorDescription, $statusCode = Response::HTTP_BAD_REQUEST)
    {
        $this->assertSame($statusCode, $this->getDriver()->getStatusCode());

        $this->jsonContext->theJsonNodeShouldBeEqualTo(Response::HTTP_NOT_FOUND === $this->getDriver()->getStatusCode() ? 'title' : 'hydra:title', 'An error occurred');
        $this->jsonContext->theJsonNodeShouldBeEqualTo('detail', $errorDescription);
    }

    /**
     * Checks, that given JSON node is superior to the given number.
     *
     * @Then the JSON node :node should be superior to the number :number
     */
    public function theJsonNodeShouldBeSuperiorToTheNumber($node, int $number)
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        if ($actual <= (int) $number) {
            throw new ExpectationException(\sprintf('The node value is `%s` is not superior to %d', json_encode($actual), $number), $this->getDriver());
        }
    }

    /**
     * Checks, that given JSON node is inferior to the given number.
     *
     * @Then the JSON node :node should be inferior to the number :number
     */
    public function theJsonNodeShouldBeInferiorToTheNumber($node, int $number)
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        if ($actual >= (int) $number) {
            throw new ExpectationException(\sprintf('The node value is `%s` is not inferior to %d', json_encode($actual), $number), $this->getDriver());
        }
    }

    /**
     * Checks that given JSON node is newer than 1 minute ago.
     *
     * @Then the JSON node :node should be newer than 1 minute ago
     */
    public function theJsonNodeShouldBeSuperiorToTheMinute($node)
    {
        $dateForTest = new \DateTime('1 minute ago');

        $json = $this->getJson();

        $actual = null === $this->inspector->evaluate($json, $node) ? '30 years ago' : $this->inspector->evaluate($json, $node);

        $actualDate = new \DateTime($actual);

        if ($actualDate < $dateForTest) {
            throw new ExpectationException(\sprintf('The node value %s is not superior to %s', $actualDate->format('Y-m-d H:i:s'), $dateForTest->format('Y-m-d H:i:s')), $this->getDriver());
        }
    }

    /**
     * @Then the JSON node :node should contain today's date
     */
    public function theJsonNodeShouldContainTodaysDate($node)
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        $this->assertContains(date('Y-m-d'), (string) $actual);
    }

    /**
     * Checks, that given JSON node is not equal to the given string.
     *
     * @Then the JSON node :node should not be equal to the string :text
     */
    public function theJsonNodeShouldNotBeEqualToTheString($node, $text)
    {
        $this->not(
            fn () => $this->jsonContext->theJsonNodeShouldBeEqualToTheString($node, $text),
            \sprintf("The node '%s' s equal '%s'.", $node, $text)
        );
    }

    /**
     * Checks, that given JSON node is equal to the given number.
     *
     * @Then the JSON node :node should not be equal to the number :number
     */
    public function theJsonNodeShouldNotBeEqualToTheNumber($node, $number)
    {
        $this->not(
            fn () => $this->jsonContext->theJsonNodeShouldBeEqualToTheNumber($node, $number),
            \sprintf("The node '%s' s equal '%d'.", $node, $number)
        );
    }

    /**
     * @Then the JSON node :node should not be equal to :tex
     */
    public function theJsonNodeShouldNotBeEqualTo($node, $text)
    {
        $this->not(
            fn () => $this->jsonContext->theJsonNodeShouldBeEqualTo($node, $text),
            \sprintf("The node '%s' s equal '%s'.", $node, $text)
        );
    }

    /**
     * @Then the JSON node :node should not have :count element(s)
     */
    public function theJsonNodeShouldNotHaveElements($node, $count)
    {
        $this->not(
            fn () => $this->jsonContext->theJsonNodeShouldHaveElements($node, $count),
            \sprintf("The node '%s' have '%s' elements.", $node, $count)
        );
    }

    /**
     * @Then the JSON node :node should be empty
     */
    public function theJsonNodeShouldBeEmpty($node): void
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);

        if (!empty($actual)) {
            throw new \Exception(\sprintf("The node '%s' value is '%s', empty expected", $node, json_encode($actual)));
        }
    }

    private function getJson()
    {
        return new Json($this->httpCallResultPool->getResult()->getValue());
    }
}
