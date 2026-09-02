<?php

declare(strict_types=1);

namespace Tests\Behat\Context;

use Behat\Mink\Exception\ExpectationException;
use Behatch\Context\BrowserContext;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use WebDriver\Exception\StaleElementReference;

/**
 * Patched version of Behatch\Context\BrowserContext that guards against
 * Page::find() returning null during a page transition.
 *
 * The upstream loop calls $node->getText() without checking $node, which
 * produces a fatal "Call to a member function getText() on null" when the
 * DOM is briefly unavailable after a navigation (e.g. right after pressing
 * a submit button).
 */
final class PatchedBrowserContext extends BrowserContext
{
    /**
     * @Then /^(?:|I )wait (?P<count>\d+) seconds? until I see "(?P<text>[^"]*)" in the "(?P<element>[^"]*)" element$/
     */
    public function iWaitSecondsUntilISeeInTheElement($count, $text, $element): void
    {
        $startTime = time();
        $this->iWaitSecondsForElement($count, $element);

        $expected = str_replace('\\"', '"', (string) $text);
        $message = \sprintf("The text '%s' was not found after a %s seconds timeout", $expected, $count);

        do {
            try {
                usleep(1000);
                $node = $this->getSession()->getPage()->find('css', $element);
                if (null === $node) {
                    continue;
                }
                $this->assertContains($expected, $node->getText(), $message);

                return;
            } catch (ExpectationException $e) {
                /* Intentionally leave blank */
            } catch (StaleElementReference|StaleElementReferenceException) {
                // assume page reloaded whilst we were still waiting
            }
        } while (time() - $startTime < $count);

        $node = $this->getSession()->getPage()->find('css', $element);
        if (null === $node) {
            throw new ExpectationException(\sprintf("Element '%s' was not found after a %s seconds timeout", $element, $count), $this->getSession()->getDriver());
        }
        $this->assertContains($expected, $node->getText(), $message);
    }

    /**
     * @When I accept the alert
     */
    public function iAcceptTheAlert(): void
    {
        $this->getSession()->executeScript('window.confirm = () => true');
    }
}
