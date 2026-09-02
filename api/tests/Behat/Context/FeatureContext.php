<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use App\Manager\UserManager;
use App\Security\JWT\JWTEncoder;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Exception\ExpectationException;
use Behatch\Context\RestContext as BaseRestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class FeatureContext implements Context
{
    use AssertionTrait;
    use KernelAwareTrait;

    protected BaseRestContext $restContext;
    protected array $headers = [];

    private readonly IriConverterInterface $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

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
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope)
    {
        foreach ($this->headers as $name) {
            $this->iAddHeaderEqualTo($name, '');
        }

        $this->headers = [];
    }

    /**
     * @Given I authenticate as the :portal user :username
     */
    public function iAuthenticateAsPortalUser($username, $portal)
    {
        $this->iAuthenticateAs($username, $portal);
    }

    /**
     * @Given I authenticate as the authorized application :application
     */
    public function iAuthenticateAsTheAuthorizedApplication($application)
    {
        $this->iAuthenticateAs($application, 'external');
    }

    /**
     * @Then the resource :resource should only be available for :portals user(s)
     */
    public function resourceIsAvailableForUsers(string $class, string $portals): void
    {
        try {
            $iri = $this->iriConverter->getIriFromResource($class, UrlGeneratorInterface::ABS_PATH, new GetCollection());
        } catch (InvalidArgumentException $e) {
            throw new ExpectationException(\sprintf('No exposed resource found for class %s', $class), $this->getDriver());
        }

        foreach ($iris = [$iri, \sprintf('%s/1', $iri)] as $url) {
            $this->restContext->iSendARequestTo(Request::METHOD_GET, $this->restContext->locatePath($url));
            $this->restContext->getMink()->assertSession()->statusCodeEquals(Response::HTTP_UNAUTHORIZED);
        }

        $allowedPortals = explode(',', str_replace(' ', '', $portals));
        foreach (['intranet' => 'user-basic@tld.fr', 'extranet' => 'julien.lepers@tld.com', 'evendors' => 'vendor.user@vendor.fr'] as $portal => $username) {
            foreach ($iris as $iri) {
                $this->restContext->iSendARequestTo(Request::METHOD_GET, $this->restContext->locatePath($iri));
                $this->restContext->getMink()->assertSession()->statusCodeEquals(Response::HTTP_UNAUTHORIZED);

                $this->iAuthenticateAs($username, $portal);
                $this->restContext->iSendARequestTo(Request::METHOD_GET, $this->restContext->locatePath($iri));
                $code = \in_array($portal, $allowedPortals, true) ? Response::HTTP_OK : Response::HTTP_FORBIDDEN;

                $this->restContext->getMink()->assertSession()->statusCodeEquals($code);
            }
        }
    }

    protected function iAddHeaderEqualTo($header, $value)
    {
        $this->headers[] = $header;
        $this->restContext->iAddHeaderEqualTo(mb_strtoupper((string) $header), (string) $value);
    }

    private function iAuthenticateAs($username, $portal)
    {
        $jwtManager = $this->getContainer()->get('lexik_jwt_authentication.jwt_manager');

        $loader = $this->getContainer()->get(UserManager::class)->getUserLoader($portal);

        if (null === $loader) {
            throw new UserNotFoundException(\sprintf('User "%s" not found.', $username));
        }

        $user = $loader($username);
        $this->assertNotNull($user, "Couldn't load user '$username'");

        $request = new Request();
        $request->request->set(UserManager::LOGIN_PORTAL, $portal);
        $this->getContainer()->get('request_stack')->push($request);
        $jwt = $jwtManager->createFromPayload($user, [JWTEncoder::PAYLOAD_USER_KEY => $user]);
        $this->iAddHeaderEqualTo('Authorization', 'Bearer '.$jwt);
    }
}
