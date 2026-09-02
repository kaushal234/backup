<?php

declare(strict_types=1);

namespace Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Step\When;
use Behatch\Asserter;
use Facebook\WebDriver\Exception\NoSuchAlertException;
use Mink\WebdriverClassicDriver\WebdriverClassicDriver;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class FeatureContext extends MinkContext implements Context
{
    use Asserter;

    public const SESSION_NAME_SELENIUM4 = 'selenium4';

    public const BASE_PATH = '/en/private';

    private readonly FilesystemAdapter $cache;

    private bool $useSessionCache = true;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly KernelInterface $kernel,
        private readonly OutputInterface $output = new ConsoleOutput(),
    ) {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * Behat/Mink is using Selenium2Driver for behat configuration.
     * But we're using Selenium 4 and Selenium4Driver is actually not exist.
     * So we override the current session with WebdriverClassicDriver with Selenium4 configuration.
     *
     * This method also switching between Selenium and default browserkit.
     *
     * @BeforeScenario
     *
     * @throws \ReflectionException
     */
    public function switchSession(BeforeScenarioScope $scope): void
    {
        $sessionName = 'default';
        $isDebug = $this->kernel->isDebug();

        // Define session name to Selenium if javascript tag is present
        if (\in_array('javascript', $scope->getScenario()->getTags(), true)) {
            $sessionName = self::SESSION_NAME_SELENIUM4;
        }

        if (\in_array('authentication', $scope->getScenario()->getTags(), true)) {
            $this->useSessionCache = false;
        }

        // Set default session
        if ($this->getMink()->hasSession($sessionName)) {
            $this->getMink()->setDefaultSessionName($sessionName);

            return;
        }

        // Get Selenium HUB url and browser name of behat configuration
        $driver = $this->getSession()->getDriver();
        $sessionReflected = new \ReflectionObject($driver);
        $webDriverPropertyReflected = $sessionReflected->getProperty('webDriver');
        $webDriver = $webDriverPropertyReflected->getValue($driver);
        $browserNamePropertyReflected = $sessionReflected->getProperty('browserName');
        $browserName = $browserNamePropertyReflected->getValue($driver);

        // Override selenium2 session
        if ($driver instanceof Selenium2Driver) {
            $session = new Session(new WebdriverClassicDriver($browserName, [
                'browserName' => $browserName,
                'acceptInsecureCerts' => true,
                'pageLoadStrategy' => 'eager',
                'goog:chromeOptions' => [
                    'args' => array_merge([
                        '--disable-web-security',
                        '--allow-running-insecure-content',
                        '--window-size=1920,1080',
                        '--blink-settings=imagesEnabled=false',
                        '--no-first-run',
                        '--disable-extensions',
                        '--disable-background-timer-throttling',
                        '--disable-renderer-backgrounding',
                        '--disable-backgrounding-occluded-windows',
                        '--mute-audio',
                    ], $isDebug ? [] : [
                        '--headless=new',
                        '--no-sandbox',
                    ]),
                    'prefs' => [
                        'safebrowsing.enabled' => false,
                    ],
                ],
            ], $webDriver->getUrl()));

            $this->getMink()->registerSession(self::SESSION_NAME_SELENIUM4, $session);
            $this->getMink()->setDefaultSessionName(self::SESSION_NAME_SELENIUM4);
        }
    }

    /**
     * Selenium is waiting an absolute URL while BrowserKit is waiting a relative URL.
     * So we have to switch url depending on session. And add a base path to URL when we use Selenium.
     *
     * @BeforeStep
     */
    public function switchBaseUrl(): void
    {
        if (self::SESSION_NAME_SELENIUM4 === $this->getMink()->getDefaultSessionName()) {
            $currentUrl = $this->getMinkParameter('base_url');
            if (!str_contains($currentUrl, self::BASE_PATH)) {
                $this->setMinkParameter('base_url', $currentUrl.self::BASE_PATH);
            }
        }
    }

    /**
     * Before each Selenium step, wait for any Symfony UX Live Component to finish
     * re-rendering. This eliminates ElementClickInterceptedException caused by the
     * busy="" overlay without needing explicit wait steps in feature files.
     *
     * @BeforeStep
     */
    public function waitForLiveComponentsBeforeStep(): void
    {
        if (self::SESSION_NAME_SELENIUM4 !== $this->getMink()->getDefaultSessionName()) {
            return;
        }

        try {
            $this->waitForLiveComponentsReady();
        } catch (\Exception) {
            // Page may be navigating; the next step will handle any real error.
        }
    }

    /**
     * Save screenshot when a test fails.
     * Only on Selenium session.
     *
     * @AfterStep
     */
    public function saveScreenshotOnFailure(AfterStepScope $scope): void
    {
        if (!$scope->getTestResult()->isPassed()
            && self::SESSION_NAME_SELENIUM4 === $this->getMink()->getDefaultSessionName()) {
            $filePath = $this->kernel->getCacheDir().'/';
            $fileName = \sprintf(
                '%s_feature_%s_step_%s.jpg',
                $scope->getSuite()->getName(),
                $scope->getFeature()->getLine(),
                $scope->getStep()->getLine()
            );
            $this->saveScreenshot($fileName, $filePath);

            // If HOST_PATH is set, it will not display the absolute path of container.
            // But the host path of local machine. This will allow developer to click the link in terminal.
            if ($hostPath = getenv('HOST_PATH')) {
                $filePath = $hostPath.str_replace(realpath($this->kernel->getProjectDir()), '/intranet', $filePath);
            }

            $this->output->writeln("\r\n\r\n<info>Screenshot</info> : file://$filePath$fileName\r\n");
        }
    }

    /**
     * @Given I am authenticated as :username with :password
     * @Given I authenticate as :username with :password
     *
     * @throws ExpectationException
     */
    public function iAmAuthenticatedAs(string $username, string $password): void
    {
        $cacheKey = 'session_test_'.str_replace('@', '_', $username);
        $value = $this->cache->getItem($cacheKey);
        if ($value->isHit()
            && $this->useSessionCache
            && self::SESSION_NAME_SELENIUM4 !== $this->getMink()->getDefaultSessionName()) {
            $cookiesData = json_decode($value->get(), true);
            $this->getSession()->setCookie('COOKIE_CAT', $cookiesData['cookieCat']);
            $this->getSession()->setCookie('MOCKSESSID', $cookiesData['mockSessId']);
            $this->getSession()->setCookie('ALVEST_BUSINESS_UNIT', $cookiesData['businessUnit']);

            return;
        }

        $this->iAmOnHomepage();
        $this->fillField('_username', $username);
        $this->fillField('_password', $password);
        $this->pressButton('_submit');
        try {
            // ClassicDriver do not return a response header.
            // So we can't check response status code with Selenium.
            if (self::SESSION_NAME_SELENIUM4 !== $this->getMink()->getDefaultSessionName()) {
                $this->assertResponseStatus(200);
                $this->assertPageAddressIsNot('/login');

                $this->cache->get($cacheKey, function (ItemInterface $item): string {
                    $item->expiresAfter(3600);

                    return json_encode([
                        'cookieCat' => $this->getSession()->getCookie('COOKIE_CAT'),
                        'mockSessId' => $this->getSession()->getCookie('MOCKSESSID'),
                        'businessUnit' => $this->getSession()->getCookie('ALVEST_BUSINESS_UNIT'),
                    ]);
                });

            // Wait the homepage is loaded for Selenium
            } else {
                // This part is really sensible, because when we get the first page after login on the first test job,
                // We have to wait the page is loaded and cache is warmup. So we check the element #ticket-helper is
                // loaded (because it's the last element of the page), and we wait maximum 10 seconds.
                // Second problem, the page have to be fully loaded, or it cause some CSRF problem. So we just add a
                // one second break.
                $this->waitPageElement('#ticket-helper');
            }
        } catch (ExpectationException $e) {
            throw new ExpectationException('Authentication failed, or homepage is unreachable.', $this->getSession()->getDriver());
        }
    }

    /**
     * Checks, that current page PATH is not equal to specified.
     *
     * @Then /^(?:|I )should not be on "(?P<page>[^"]+)"$/
     *
     * @throws ExpectationException
     */
    public function assertPageAddressIsNot(string $page): void
    {
        $this->assertSession()->addressNotEquals($this->locatePath($page));
    }

    /**
     * Perform a click on HTML element ID.
     *
     * @When /^(?:|I )click on element ID "(?P<elementId>[^"]+)"$/
     */
    public function clickOnElementId(string $elementId): void
    {
        $this->getSession()->getPage()->findById($elementId)->click();
    }

    /**
     * @Then I should see response headers :header with :value
     *
     * @throws ExpectationException when the condition is not fulfilled
     */
    public function iShouldSeeResponseHeadersWith(string $header, string $value): void
    {
        $headers = $this->getSession()->getResponseHeaders();
        if (!\array_key_exists($header, $headers)) {
            throw new ExpectationException(\sprintf("The header '%s' is not present. The list of present headers are '%s'", $header, implode("', '", array_keys($headers))), $this->getSession()->getDriver());
        }
        $contentTypeValue = current($headers[$header]);
        if ($contentTypeValue !== $value) {
            throw new ExpectationException(\sprintf("The header '%s' value is '%s' but '%s' was expected", $header, $contentTypeValue, $value), $this->getSession()->getDriver());
        }
    }

    /**
     * @Then The module should be :module
     *
     * @throws ExpectationException
     */
    public function theModuleShouldBe(string $module): void
    {
        $this->iShouldSeeResponseHeadersWith('x-alvest-module', $module);
    }

    /**
     * @Then I should be on a legacy page
     *
     * @throws ExpectationException
     */
    public function iShouldBeOnALegacyPage(): void
    {
        $this->assertResponseStatus(200);
        $this->iShouldSeeResponseHeadersWith('x-is-legacy', '1');
    }

    /**
     * @Then I should not be on a legacy page
     *
     * @throws ExpectationException
     */
    public function iShouldNotBeOnALegacyPage(): void
    {
        $headers = $this->getSession()->getResponseHeaders();
        $this->assertResponseStatus(200);
        if (\array_key_exists('x-is-legacy', $headers)) {
            throw new ExpectationException(\sprintf('The legacy header has been found (current url: %s)', $this->getMink()->getSession()->getCurrentUrl()), $this->getSession()->getDriver());
        }
    }

    /**
     * @Then I should be on the exact url :url
     */
    public function assertExactPageAddress(string $url): void
    {
        $this->assertPageAddress($url);
    }

    /**
     * @Then /^the "(?P<element>[^"]*)" element (?P<sign>should|should not) contain an? (?P<type>integer|text|email|phone number|country|yes or no)$/
     *
     * @throws ExpectationException
     * @throws ElementNotFoundException
     */
    public function assertElementContainType(string $element, string $type, string $not): void
    {
        $node = $this->assertSession()->elementExists('css', $element);
        $text = $node->getText();
        $not = 'should' === $not;

        $this->assertTypeValue($type, $text, $not);
    }

    /**
     * Checks, that element with specified CSS have the specific type.
     *
     * @Then /^the "(?P<field>(?:[^"]|\\")*)" field (?P<sign>should|should not) contain an? (?P<type>integer|text|email|phone number|country|yes or no)$/
     *
     * @throws ExpectationException
     */
    public function assertFieldContainType(string $field, string $type, string $not): void
    {
        $node = $this->assertSession()->fieldExists($field);
        $text = $node->getValue();
        $not = 'should' === $not;

        $this->assertTypeValue($type, $text, $not);
    }

    /**
     * Useful when you're testing with VNC viewer to stop the test.
     * Don't forget to restart Selenium.
     *
     * @Then I exit
     */
    public function exit(): void
    {
        exit;
    }

    /**
     * Wait until element exist.
     *
     * @Then I wait for the AJAX results to appear in :element
     *
     * @deprecated
     */
    public function iWaitForTheAjaxResultsToAppear(string $element): void
    {
        $this->getSession()->wait(2000, \sprintf("document.querySelector('%s') !== null", $element));
    }

    /**
     * Focus on the element and type text.
     * Actually use for react select choice.
     *
     * @When I focus on the select field :field and type :text
     *
     * @throws ElementNotFoundException
     * @throws DriverException
     * @throws UnsupportedDriverActionException
     * @throws \ReflectionException
     *
     * @deprecated ReactChoice should be replaced by AutocompleteChoice
     */
    public function iFocusOnTheSelectFieldAndType($field, $text): void
    {
        $session = $this->getSession();
        $page = $session->getPage();

        // Find select and focus on it
        $selectField = $page->find('css', $field);
        if (!$selectField) {
            throw new ElementNotFoundException($session, 'Select field', 'id', $field);
        }
        $session->getDriver()->click($selectField->getXpath());

        // Find input field
        $selectInputField = $page->find('css', $field.' input');
        if (!$selectInputField) {
            throw new ElementNotFoundException($session, 'Select input field', 'id', $field.' input');
        }

        // Get RemoteElement to trigger a keypress with Selenium.
        // Usually use setValue of driver, but it was applying a blur after key press,
        // which close react select.
        $method = new \ReflectionMethod(WebdriverClassicDriver::class, 'findElement');
        $method->setAccessible(true);
        $element = $method->invoke($this->getSession()->getDriver(), $selectInputField->getXpath());
        $element->sendKeys($text);
    }

    /**
     * Override attachFileToField to resolve the file path from the Selenium container volume.
     * Falls back to the standard Mink files_path resolution for non-Selenium sessions.
     *
     * @override When /^(?:|I )attach the file "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)"$/
     *
     * @throws \Exception
     */
    public function attachFileToField($field, $path): void
    {
        $field = $this->fixStepArgument($field);

        if (self::SESSION_NAME_SELENIUM4 === $this->getMink()->getDefaultSessionName()) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), \DIRECTORY_SEPARATOR)
                .\DIRECTORY_SEPARATOR.$path;

            $driver = $this->getSession()->getDriver();
            $reflectedDriver = new \ReflectionObject($driver);
            $webDriverProp = $reflectedDriver->getProperty('webDriver');
            $innerWebDriver = $webDriverProp->getValue($driver);

            $element = $innerWebDriver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector(\sprintf('input[name="%s"]', $field)));
            $element->setFileDetector(new \Facebook\WebDriver\Remote\LocalFileDetector());
            $element->sendKeys($fullPath);

            return;
        }

        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR.$path;
            if (is_file($fullPath)) {
                $path = $fullPath;
            }
        }

        $this->getSession()->getPage()->attachFileToField($field, $path);
    }

    /**
     * Override fillField of mink context to add scroll with Selenium.
     *
     * @override When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @override /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with:$/
     * @override /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
     *
     * @throws ElementNotFoundException
     */
    public function fillField($field, $value): void
    {
        $field = $this->fixStepArgument($field);
        $value = $this->fixStepArgument($value);
        if (self::SESSION_NAME_SELENIUM4 === $this->getMink()->getDefaultSessionName()) {
            $this->getSession()->getPage()->findField($field)->click();
        }
        $this->getSession()->getPage()->fillField($field, $value);
    }

    /**
     * Override fillFields of mink context to add scroll with Selenium.
     *
     * @override When /^(?:|I )fill in the following:$/
     *
     * @throws ElementNotFoundException
     */
    public function fillFields(TableNode $fields): void
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->fillField($field, $value);
        }
    }

    /**
     * Override pressButton to use a JS click in Selenium, bypassing any z-index overlay that intercepts native clicks.
     * Falls back to standard Mink pressButton for non-Selenium sessions or when no button is found.
     *
     * @override When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)"$/
     */
    public function pressButton($button): void
    {
        $button = $this->fixStepArgument($button);

        if (self::SESSION_NAME_SELENIUM4 !== $this->getMink()->getDefaultSessionName()) {
            $this->getSession()->getPage()->pressButton($button);

            return;
        }

        $encoded = json_encode($button, \JSON_UNESCAPED_UNICODE);

        // Try native click first; if a z-index overlay intercepts it, fall back to JS click.
        try {
            $this->getSession()->getPage()->pressButton($button);

            return;
        } catch (\Facebook\WebDriver\Exception\ElementClickInterceptedException) {
            // Swallow — JS click below will succeed regardless of visual overlays.
        }

        $clicked = (bool) $this->getSession()->evaluateScript(<<<JS
            (function() {
                const byValue = document.querySelector('input[type="submit"][value={$encoded}], button[value={$encoded}]');
                if (byValue) { byValue.click(); return true; }
                const allButtons = Array.from(document.querySelectorAll('button, input[type="submit"]'));
                const match = allButtons.find(b => (b.textContent || b.value || '').trim() === {$encoded});
                if (match) { match.click(); return true; }
                return false;
            })()
        JS);

        if (!$clicked) {
            $this->getSession()->getPage()->pressButton($button);
        }
    }

    /**
     * Override selectOption to use JS-based selection with Selenium (supports Tom Select and React selects).
     * Falls back to the standard Mink selectFieldOption for non-Selenium sessions.
     *
     * @override When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)"$/
     */
    public function selectOption($select, $option): void
    {
        $select = $this->fixStepArgument($select);
        $option = $this->fixStepArgument($option);
        $element = $this->getSession()->getPage()->findField($select);
        $session = $this->getSession();

        if (self::SESSION_NAME_SELENIUM4 === $this->getMink()->getDefaultSessionName()) {
            $this->forceOptionWithJS($select, $option);
        } elseif ($element->getAttribute('data-controller')
            && str_contains($element->getAttribute('data-controller'), 'autocomplete')) {
            $this->disableSelectConstraintOnOptions($select, $option);
            $session->getDriver()->selectOption($element->getXpath(), $option);

            return;
        }

        $session->getPage()->selectFieldOption($select, $option);
    }

    /**
     * Override additionallySelectOption of mink context.
     *
     * @override When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)"$/
     *
     * @throws ElementNotFoundException
     */
    public function additionallySelectOption($select, $option)
    {
        $select = $this->fixStepArgument($select);
        $option = $this->fixStepArgument($option);
        $element = $this->getSession()->getPage()->findField($select);
        $session = $this->getSession();

        if (self::SESSION_NAME_SELENIUM4 === $this->getMink()->getDefaultSessionName()) {
            $this->forceOptionWithJS($select, $option);
        } elseif ($element->getAttribute('data-controller')
            && str_contains($element->getAttribute('data-controller'), 'autocomplete')) {
            $this->disableSelectConstraintOnOptions($select, $option);
            $session->getDriver()->selectOption($element->getXpath(), $option, true);

            return;
        }

        $session->getPage()->selectFieldOption($select, $option, true);
    }

    /**
     * Override checkOption to support custom checkboxes with Selenium via a JS click.
     * A JS click bypasses the Live Component loading overlay, which can intercept a real
     * click while a debounced model-update request is in flight (same approach as iCheckRadio).
     * Falls back to the standard Mink checkField for non-Selenium sessions or when no element is found.
     *
     * @override When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)"$/
     */
    public function checkOption($option): void
    {
        $option = $this->fixStepArgument($option);

        if (self::SESSION_NAME_SELENIUM4 === $this->getMink()->getDefaultSessionName()
            && $this->clickCheckboxWithJS($option)) {
            return;
        }

        $this->getSession()->getPage()->checkField($option);
    }

    /**
     * Override uncheckOption to support custom checkboxes with Selenium via a JS click.
     * A JS click bypasses the Live Component loading overlay, which can intercept a real
     * click while a debounced model-update request is in flight (same approach as iCheckRadio).
     * Falls back to the standard Mink uncheckField for non-Selenium sessions or when no element is found.
     *
     * @override When /^(?:|I )unCheck "(?P<option>(?:[^"]|\\")*)"$/
     */
    public function uncheckOption($option): void
    {
        $option = $this->fixStepArgument($option);

        if (self::SESSION_NAME_SELENIUM4 === $this->getMink()->getDefaultSessionName()
            && $this->clickCheckboxWithJS($option)) {
            return;
        }

        $this->getSession()->getPage()->uncheckField($option);
    }

    /**
     * @When I click on the :selector element and accept confirm
     */
    public function iClickAndAcceptConfirm(string $selector): void
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $selector);

        if (!$element) {
            throw new \Exception("Element not found: $selector");
        }

        $element->click();

        // Retrieve WebDriver via reflection because getWebDriver() is protected
        $driver = $this->getSession()->getDriver();
        $webDriver = null;

        $refClass = new \ReflectionClass($driver);
        if ($refClass->hasMethod('getWebDriver')) {
            $method = $refClass->getMethod('getWebDriver');
            $method->setAccessible(true);
            $webDriver = $method->invoke($driver);
        }

        if (null === $webDriver) {
            throw new \Exception('Unable to access WebDriver');
        }

        // Poll for the alert instead of sleeping a fixed duration.
        $deadline = microtime(true) + 3.0;
        while (microtime(true) < $deadline) {
            try {
                $webDriver->switchTo()->alert()->accept();

                return;
            } catch (NoSuchAlertException) {
                usleep(100000); // 100 ms between attempts
            }
        }
    }

    /**
     * @Then I take a screenshot
     */
    public function iTakeAScreenshot(): void
    {
        if (self::SESSION_NAME_SELENIUM4 !== $this->getMink()->getDefaultSessionName()) {
            return;
        }

        $filePath = $this->kernel->getCacheDir().'/';
        $fileName = \sprintf('screenshot_%s.jpg', date('Ymd_His'));

        $this->saveScreenshot($fileName, $filePath);

        if ($hostPath = getenv('HOST_PATH')) {
            $filePath = $hostPath.str_replace(realpath($this->kernel->getProjectDir()), '/intranet', $filePath);
        }

        $this->output->writeln("\n\n<info>Screenshot</info>: file://$filePath$fileName\n");
    }

    /**
     * @Then I fill in dropdown :name with :option
     */
    public function iFillInDropdown($name, $option)
    {
        $session = $this->getSession();
        $page = $session->getPage();

        $this->typeInInput($name, $option);

        $appeared = $session->wait(
            5000,
            "document.querySelectorAll('[id*=\"react-select-\"][id*=\"-option-\"]').length > 0"
        );
        if (!$appeared) {
            throw new \Exception(\sprintf("Dropdown '%s' did not populate any option for '%s'", $name, $option));
        }

        // React-select re-renders its option list asynchronously while we iterate,
        // which detaches the DOM nodes and yields "stale element reference" on
        // $node->getText() / $node->click(). Re-query on every attempt and retry
        // on any driver-level transient error until the deadline.
        $deadline = microtime(true) + 3.0;
        $lastSeen = [];
        $matched = false;
        $lowerOption = mb_strtolower($option);

        while (microtime(true) < $deadline && !$matched) {
            try {
                $options = $page->findAll('css', '[id*="react-select-"][id*="-option-"]');
                if ([] === $options) {
                    usleep(50000);
                    continue;
                }

                $texts = [];
                foreach ($options as $currentOption) {
                    $texts[] = trim($currentOption->getText());
                }
                $lastSeen = $texts;

                foreach ($options as $idx => $currentOption) {
                    if (mb_strtolower($texts[$idx]) === $lowerOption) {
                        $currentOption->click();
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    foreach ($options as $idx => $currentOption) {
                        if (false !== mb_stripos($texts[$idx], $option)) {
                            $currentOption->click();
                            $matched = true;
                            break;
                        }
                    }
                }
                if (!$matched) {
                    // Options are populated but don't contain our target yet;
                    // the list may still be updating from a fresh render.
                    usleep(50000);
                }
            } catch (DriverException|\Facebook\WebDriver\Exception\WebDriverException $e) {
                // Stale element, detached node, or other transient driver error.
                // WebdriverClassicDriver does not wrap exceptions thrown from
                // $element->click() or $element->getDomProperty(), so the raw
                // Facebook StaleElementReferenceException can bubble up here.
                usleep(50000);
            }
        }

        if (!$matched) {
            throw new \Exception(\sprintf("Option '%s' not found in dropdown '%s' (last seen: %s)", $option, $name, '' !== implode(' | ', $lastSeen) ? implode(' | ', $lastSeen) : '<none>'));
        }
    }

    /**
     * @Then I fill in rich textarea :name with :value
     */
    public function iFillInRichTextArea($name, $value)
    {
        $session = $this->getSession();
        $session->executeScript("
            let input = undefined;
            input = document.querySelector('.generic_rich_text_field__wrapper.$name .ck-editor__editable');
            if (!input) {
                input = document.querySelector('textarea[name=\"$name\"]');
            }
            if (input) {
                const editorInstance = input.ckeditorInstance;
                editorInstance.setData('$value');
                editorInstance.updateSourceElement();
            }
        ");
    }

    /**
     * @Then I check radio :name with :value
     */
    public function iCheckRadio($name, $value)
    {
        $session = $this->getSession();
        $session->executeScript("
            const input = document.querySelector('.generic_radio__wrapper input[name=$name][value=$value]');
            if (input) {
                input.click()
            } else {
                throw new Error(`Radio input not found: $name with value: $value`);
            }
        ");
    }

    /**
     * @Then I fill in date picker :name with :date
     */
    public function iSelectFromDatePicker($name, $date)
    {
        if ('today' === $date) {
            $this->typeInInput($name, date('Y-m-d'));
        } else {
            $this->typeInInput($name, $date);
        }
    }

    /**
     * @Then I trigger a screenshot error
     */
    public function iTriggerScreenshotError()
    {
        throw new \Exception('🖼️ Screenshot trigger: Intentional error for debugging.');
    }

    /**
     * @Then I scroll :value and trigger a screenshot error
     */
    public function iScrollAndTriggerScreenshotError($value)
    {
        $session = $this->getSession();
        $session->executeScript("
            window.scrollTo(0,$value);
        ");
        throw new \Exception('🖼️ Screenshot trigger: Intentional error for debugging.');
    }

    /**
     * Add option into autocomplete HTML element.
     */
    protected function forceOptionWithJS($select, $option): void
    {
        $element = $this->getSession()->getPage()->findField($select);

        if ($element->hasClass('ts-hidden-accessible')
            && !$element->find('xpath', ".//option[@value='$option']")) {
            $this->getSession()->executeScript(<<<JS
                    const element = document.querySelector('select[name="$select"]');
                    const option = document.createElement('option');
                    option.value = '$option';
                    element.appendChild(option);
               JS);
        }
    }

    /**
     * By pass the browserkitdriver constraint for autocomplete select.
     */
    protected function disableSelectConstraintOnOptions($select, $option): void
    {
        $element = $this->getSession()->getPage()->findField($select);

        $method = new \ReflectionMethod(BrowserKitDriver::class, 'getFormField');
        $method->setAccessible(true);
        $formField = $method->invoke($this->getSession()->getDriver(), $element->getXpath());
        if ($formField instanceof ChoiceFormField) {
            $formField->disableValidation();
        }
    }

    /**
     * Wait until element exists.
     *
     * @throws ElementNotFoundException
     */
    protected function waitPageElement(string $element, int $maxSeconds = 10): void
    {
        $appeared = $this->getSession()->wait(
            $maxSeconds * 1000,
            \sprintf("document.querySelector('%s') !== null", addslashes($element))
        );

        if (!$appeared) {
            throw new ElementNotFoundException($this->getSession(), 'Element', 'css', $element);
        }

        $this->waitForAppReady();
    }

    /**
     * Wait until the document is fully parsed. Does NOT wait for in-flight AJAX,
     * because the intranet base layout fires a background notifications request
     * on every page load that would otherwise block every step.
     * Returns immediately on non-Selenium sessions.
     */
    protected function waitForAppReady(int $maxSeconds = 5): void
    {
        if (self::SESSION_NAME_SELENIUM4 !== $this->getMink()->getDefaultSessionName()) {
            return;
        }

        $this->getSession()->wait(
            $maxSeconds * 1000,
            "document.readyState === 'complete'"
        );
    }

    /**
     * Wait until no Symfony UX Live Component is in its busy (re-rendering) state.
     * During every AJAX model update, the component root div holds busy="", which overlays
     * child elements and causes ElementClickInterceptedException.
     * Returns immediately (~1 ms) when no component is busy.
     */
    protected function waitForLiveComponentsReady(int $maxSeconds = 5): void
    {
        if (self::SESSION_NAME_SELENIUM4 !== $this->getMink()->getDefaultSessionName()) {
            return;
        }

        $this->getSession()->wait(
            $maxSeconds * 1000,
            'document.querySelectorAll(\'[data-controller="live"][busy]\').length === 0'
        );
    }

    /**
     * Toggle a (custom) checkbox via a JS click, returning whether the element was found.
     *
     * Original, unchanged behavior: look the element up by id, falling back to its associated
     * label (label[for=option]) — e.g. "terms"/"conditions" on the sample form, or React fields
     * like "indefinitePeriodType". Whatever is found is clicked directly, exactly as before.
     *
     * New fallback, only reached when BOTH of the above find nothing: a Symfony form field name
     * (e.g. "customer[isContract]") has an id derived from the name (brackets stripped/
     * underscored), so neither lookup above ever matches it. In that case only, we look the input
     * up by its "name" attribute, then resolve the label from the input's real id and click the
     * label instead, since clicking that particular input directly gets intercepted by its
     * overlapping label.
     */
    private function clickCheckboxWithJS(string $option): bool
    {
        $optionSelector = json_encode($option, \JSON_UNESCAPED_UNICODE);
        $labelSelector = json_encode(\sprintf('label[for="%s"]', $option), \JSON_UNESCAPED_UNICODE);
        $nameSelector = json_encode(\sprintf('input[name="%s"]', addcslashes($option, '"\\')), \JSON_UNESCAPED_UNICODE);

        return (bool) $this->getSession()->evaluateScript(<<<JS
            (function() {
                const el = document.getElementById($optionSelector) || document.querySelector($labelSelector);
                if (el) {
                    el.click();
                    return true;
                }
                const byName = document.querySelector($nameSelector);
                if (!byName) { return false; }
                const label = byName.id ? document.querySelector('label[for="' + byName.id + '"]') : null;
                (label || byName).click();
                return true;
            })()
        JS);
    }

    /**
     * @throws ExpectationException
     */
    private function assertTypeValue(string $type, string $value, bool $sign): void
    {
        switch ($type) {
            case 'integer':
                $this->assert(
                    (bool) preg_match('#^[0-9]*$#', $value),
                    \sprintf('Element doesn\'t contain an integer, "%s" given.', $value),
                    $sign
                );
                break;
            case 'text':
                $this->assert(
                    (bool) preg_match('#^[^><]*$#', $value),
                    \sprintf('Element doesn\'t contain a text, "%s" given.', $value),
                    $sign
                );
                break;
            case 'email':
                $this->assert(
                    0 === \count($this->validator->validate($value, new Assert\Email())),
                    \sprintf('Element doesn\'t contain an email, "%s" given.', $value),
                    $sign
                );
                break;
            case 'country':
                $this->assert(
                    0 === \count($this->validator->validate($value, new Assert\Country())),
                    \sprintf('Element doesn\'t contain an country, "%s" given.', $value),
                    $sign
                );
                break;
            case 'yes or no':
                $this->assert(
                    (bool) preg_match('#^yes|no$#', $value),
                    \sprintf('Element doesn\'t contain an yes or no, "%s" given.', $value),
                    $sign
                );
                break;
            case 'phone number':
                // Faker generate this phone type : +33 # ## ## ## ##
                $this->assert(
                    (bool) preg_match('#^\+33( [0-9]{2}){5}$#', $value),
                    \sprintf('Element doesn\'t contain a phone number, "%s" given.', $value),
                    $sign
                );
                break;
        }
    }

    /**
     * Asserts a condition.
     *
     * @throws ExpectationException when the condition is not fulfilled
     */
    private function assert(bool $condition, string $message, bool $not): void
    {
        if ($not) {
            if ($condition) {
                return;
            }
        } else {
            if (!$condition) {
                return;
            }
        }

        throw new ExpectationException($message, $this->getSession()->getDriver());
    }

    private function typeInInput($name, $text)
    {
        $dropdownTypes = [
            'generic_single_select_static_dropdown__wrapper',
            'generic_single_select_dynamic_dropdown__wrapper',
            'generic_single_select_auto_complete_dropdown__wrapper',
            'generic_multi_select_static_dropdown__wrapper',
            'generic_multi_select_dynamic_dropdown__wrapper',
            'generic_multi_select_auto_complete_dropdown__wrapper',
            'generic_date_picker__wrapper',
        ];

        $selectors = [];
        foreach ($dropdownTypes as $type) {
            $selectors[] = ".$type.$name input";
        }
        $selector = implode(', ', $selectors);

        $session = $this->getSession();
        $session->executeScript("
            const input = document.querySelector('$selector');
            if (input) {
                input.focus();
                const nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                nativeInputValueSetter.call(input, '$text');
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        ");
    }
}
