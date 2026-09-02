<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behatch\Context\RestContext as BaseRestContext;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class UserContext implements Context
{
    use AssertionTrait;
    use KernelAwareTrait;

    protected BaseRestContext $restContext;

    protected array $headers = [];

    private readonly JWTEncoderInterface $encoder;

    private readonly PropertyAccessorInterface $accessor;

    private ?\stdClass $payload = null;

    private ?JWTDecodeFailureException $exception = null;

    public function __construct(JWTEncoderInterface $encoder, PropertyAccessorInterface $accessor)
    {
        $this->encoder = $encoder;
        $this->accessor = $accessor;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        /** @var BaseRestContext $restContext */
        $restContext = $environment->getContext(BaseRestContext::class);
        $this->restContext = $restContext;
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope)
    {
        foreach ($this->headers as $name) {
            $this->iAddHeaderEqualTo($name, null);
        }

        $this->headers = [];
        $this->payload = null;
        $this->exception = null;
    }

    /**
     * @Given I send a :status Token with Username :username to :url
     */
    public function iSendATokenTo($status, $username, $url)
    {
        $token = $this->generateToken($username, 'expired' === $status);
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/ld+json');
        $this->restContext->iAddHeaderEqualTo('Content-type', 'application/ld+json');
        $this->restContext->iSendARequestToWithParameters(
            'POST',
            $url,
            new TableNode([['key', 'value'], ['token', $token]])
        );
    }

    /**
     * @Then The JWT token node :node should be equal to :value
     */
    public function theJWTTokenKeyShouldBeEqualTo(string $node, $value)
    {
        if (null === $this->payload) {
            $this->decodeToken();
        }
        $this->assertSame($value, $this->accessor->getValue($this->payload, $node));
    }

    /**
     * @Then The JWT token node :node should contain :value element(s)
     */
    public function theJWTTokenKeyShouldContainElement(string $node, $value)
    {
        if (null === $this->payload) {
            $this->decodeToken();
        }
        $this->assertCount($value, $this->accessor->getValue($this->payload, $node));
    }

    /**
     * @Then The JWT token node :node should be false
     */
    public function theJWTTokenKeyShouldBeFalse(string $node)
    {
        if (null === $this->payload) {
            $this->decodeToken();
        }
        $this->assertFalse($this->accessor->getValue($this->payload, $node));
    }

    /**
     * @Then The JWT token node :node should not exist
     */
    public function theJWTTokenKeyShouldNotExist(string $node)
    {
        if (null === $this->payload) {
            $this->decodeToken();
        }

        $this->not(fn () => $this->assertArrayHasKey($node, (array) $this->payload), \sprintf("The node '%s' should not exist.", $node));
    }

    private function iAddHeaderEqualTo($header, $value)
    {
        $this->headers[] = $header;
        $this->restContext->iAddHeaderEqualTo(mb_strtoupper((string) $header), $value);
    }

    private function generateToken(string $username, bool $expired = false): string
    {
        $payload = [
            'email' => $username,
            'username' => $username,
            'upn' => $username,
            'exp' => $expired ? time() - 3600 : time() + 86400,
        ];

        return $this->encoder->encode($payload);
    }

    private function decodeToken(): void
    {
        $payload = json_decode($this->getClient()->getResponse()->getContent(), true);
        try {
            $this->payload = (object) $this->encoder->decode($payload['token'] ?? '');
            $this->exception = null;
        } catch (JWTDecodeFailureException $exception) {
            $this->exception = $exception;
            $this->payload = (object) ($exception->getPayload() ?? []);
        }
    }
}
