<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Mink\Exception\ExpectationException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mailer\DataCollector\MessageDataCollector;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class AsyncEmailContext implements Context
{
    use AssertionTrait;
    use MinkAwareTrait;

    private ?Email $emailPointer = null;

    private ?Crawler $emailDomCrawler = null;

    /**
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope)
    {
        $this->setEmailPointer();
    }

    /**
     * @Then no email should have been sent asynchronously
     * @Then /^(\d+) email(s)? should have been sent asynchronously$/
     */
    public function emailShouldHaveBeenSentAsynchronously($i = 0)
    {
        $messagesCount = \count($this->getQueuedMessages());
        $subjects = [];

        $this->assertCount($i, $this->getQueuedMessages(), \sprintf("%d email%s ha%s been sent (found '%s')", $messagesCount, $messagesCount > 1 ? 's' : '', $messagesCount > 1 ? 've' : 's', implode("','", $subjects)));
    }

    /**
     * @Then an email should have been sent asynchronously with subject :subject
     */
    public function anEmailShouldHaveBeenSentAsynchronouslyWithSubject(string $subject)
    {
        $subjects = [];

        foreach ($this->getQueuedMessages() as $message) {
            if ($subject === $result = $message->getSubject()) {
                $this->setEmailPointer($message);

                return true;
            }

            $subjects[] = $result;
        }

        throw new ExpectationException(\sprintf("Can't find an email with subject '%s' (found '%s')", $subject, implode("','", $subjects)), $this->getDriver());
    }

    /**
     * @Then an email should have been sent asynchronously with subject matching pattern :pattern
     */
    public function anEmailShouldHaveBeenSentAsynchronouslyWithSubjectMatchingPattern(string $pattern)
    {
        $subjects = [];

        foreach ($this->getQueuedMessages() as $message) {
            $result = $message->getSubject();
            if (1 === preg_match($pattern, $result)) {
                $this->setEmailPointer($message);

                return true;
            }

            $subjects[] = $result;
        }

        throw new ExpectationException(\sprintf("Can't find an email with subject matching '%s' (found '%s')", $pattern, implode("','", $subjects)), $this->getDriver());
    }

    /**
     * @Then an email should have been sent asynchronously to :to with subject matching pattern :pattern
     */
    public function anEmailShouldHaveBeenSentAsynchronouslyToWithSubjectMatchingPattern(string $pattern, string $to)
    {
        $subjects = [];

        foreach ($this->getQueuedMessages() as $message) {
            $result = $message->getSubject();
            $recipients = $this->extractAdresses($message->getTo());

            if (\in_array($to, $recipients, true) && 1 === preg_match($pattern, $result)) {
                $this->setEmailPointer($message);

                return true;
            }

            $subjects[] = \sprintf("'%s' sent to '%s'", $result, implode("', '", $recipients));
        }

        throw new ExpectationException(\sprintf("Can't find an email with subject matching '%s' sent to '%s' (found %s)", $pattern, $to, implode(' | ', $subjects)), $this->getDriver());
    }

    /**
     * @Then an email should have been sent asynchronously with subject matching pattern :pattern but not to :to
     */
    public function anEmailShouldHaveBeenSentAsynchronouslWithSubjectMatchingPatternButNotTo(string $pattern, string $to)
    {
        foreach ($this->getQueuedMessages() as $message) {
            $result = $message->getSubject();
            $recipients = $this->extractAdresses($message->getTo());

            if (!\in_array($to, $recipients, true) && 1 === preg_match($pattern, $result)) {
                return true;
            }
        }

        throw new ExpectationException(\sprintf("Email found with subject matching '%s' sent to '%s'", $pattern, $to), $this->getDriver());
    }

    /**
     * @Then no email should have been sent asynchronously with subject matching pattern :pattern
     */
    public function NoEmailShouldHaveBeenSentAsynchronouslyWithSubjectMatchingPattern(string $pattern)
    {
        foreach ($this->getQueuedMessages() as $message) {
            $result = $message->getSubject();
            if (1 <= preg_match($pattern, $result)) {
                throw new ExpectationException(\sprintf("Found an email with subject matching '%s': '%s'", $pattern, $result), $this->getDriver());
            }
        }
    }

    /**
     * @Then this asynchronous email should contain :content
     */
    public function thisAsynchronousEmailShouldContain(string $content)
    {
        $this->checkEmailPointer();

        $this->assertContains($content, $this->emailPointer->getHtmlBody());
    }

    /**
     * @Then this asynchronous email should not contain :content
     */
    public function thisAsynchronousEmailShouldNotContain(string $content)
    {
        $this->checkEmailPointer();

        $this->not(
            fn () => $this->thisAsynchronousEmailShouldContain($content),
            \sprintf("The string '%s' was found.", $content)
        );
    }

    /**
     * @Then this asynchronous email reply-to should be set to :replyTo
     */
    public function thisAsynchronousEmailShouldBeSentWith(string $replyTo)
    {
        $this->checkEmailPointer();

        $replyTos = $this->extractAdresses($this->emailPointer->getReplyTo());

        $this->assert(\in_array($replyTo, $replyTos, true), \sprintf("The reply-to is set to '%s'", implode("','", $replyTos)));
    }

    /**
     * @Given this asynchronous email should be sent from :from
     */
    public function thisAsynchronousEmailShouldBeSentFrom(string $from)
    {
        $this->checkEmailPointer();

        $froms = $this->extractAdresses($this->emailPointer->getFrom());

        $this->assert(\in_array($from, $froms, true), \sprintf("The email '%s' is not in from list", $from));
    }

    /**
     * @Given this asynchronous email should be sent only :field :tos
     * @Given this asynchronous email should be sent as :field only to :tos
     */
    public function thisAsynchronousEmailShouldBeSentOnlyTo(string $field, string $tos)
    {
        $this->checkEmailPointer();
        $expected = explode(',', str_replace(' ', '', $tos));

        $getter = 'get'.ucfirst($field);
        $actual = $this->extractAdresses($this->emailPointer->{$getter}());

        $missing = array_diff($expected, $actual);
        $additional = array_diff($actual, $expected);

        $this->assertEmpty($missing, \sprintf("Some emails are missing in %s: '%s'", $field, implode("','", $missing)));
        $this->assertEmpty($additional, \sprintf("Some unexpected emails were found in %s: '%s'", $field, implode("','", $additional)));
    }

    /**
     * @Given this asynchronous email should not be sent to :to
     */
    public function thisAsynchronousEmailShouldNotBeSentTo(string $to)
    {
        $this->checkEmailPointer();

        $tos = $this->extractAdresses($this->emailPointer->getTo());
        $this->assertArrayDoesntContain($to, $tos, \sprintf("The email '%s' is in 'to' list (found '%s')", $to, implode("','", $tos)));
    }

    /**
     * @Given this asynchronous email should be sent to :to
     */
    public function thisAsynchronousEmailShouldBeSentTo(string $to)
    {
        $this->checkEmailPointer();

        $tos = $this->extractAdresses($this->emailPointer->getTo());
        $this->assertArrayContains($to, $tos, \sprintf("The email '%s' is not in 'to' list (found '%s')", $to, implode("','", $tos)));
    }

    /**
     * @Given this asynchronous email should be sent as cc to :cc
     */
    public function thisEmailShouldBeSentAsCcTo($cc)
    {
        $this->checkEmailPointer();
        $ccs = $this->extractAdresses($this->emailPointer->getCc());
        $this->assertArrayContains($cc, $ccs, \sprintf('The email %s is not in cc list (found %s)', $cc, implode("', '", $ccs)));
    }

    /**
     * @Given this asynchronous email should be sent as bcc to :bcc
     */
    public function thisEmailShouldBeSentAsBccTo($bcc)
    {
        $this->checkEmailPointer();
        $bccs = $this->extractAdresses($this->emailPointer->getBcc());
        $this->assertArrayContains($bcc, $bccs, \sprintf('The email %s is not in cc list (found %s)', $bcc, implode("', '", $bccs)));
    }

    /**
     * @Given this asynchronous email should not be sent as cc to :cc
     */
    public function thisAsynchronousEmailShouldNotBeSentAsCcTo(string $cc)
    {
        $this->checkEmailPointer();

        $ccs = $this->extractAdresses($this->emailPointer->getCc());
        $this->assertArrayDoesntContain($cc, $ccs, \sprintf("The email '%s' is in cc list (found '%s')", $cc, implode("','", $ccs)));
    }

    /**
     * @Given this asynchronous email should not be sent as bcc to :bcc
     */
    public function thisAsynchronousEmailShouldNotBeSentAsBccTo(string $bcc)
    {
        $this->checkEmailPointer();

        $bccs = $this->extractAdresses($this->emailPointer->getBcc());
        $this->assertArrayContains($bcc, $bccs, \sprintf("The email '%s' is in bcc list (found '%s')", $bcc, implode("','", $bccs)));
    }

    /**
     * @Given this asynchronous email should have an attachment matching :pattern
     */
    public function thisAsynchronousEmailShouldHaveAFileAttached(string $pattern)
    {
        $this->checkEmailPointer();

        $files = [];
        foreach ($this->emailPointer->getAttachments() as $dataPart) {
            if (1 === preg_match('/ filename: (?<filename>.+)$/', $dataPart->asDebugString(), $matches)) {
                if (1 === preg_match($pattern, $matches['filename'])) {
                    return true;
                }

                $files[] = '"'.$matches['filename'].'"';
            }
        }

        throw new ExpectationException(\sprintf("can't find an attachment matching %s (found %s)", $pattern, implode(',', $files)), $this->getDriver());
    }

    /**
     * @Given this asynchronous email body should contain a link to :link
     */
    public function thisAsynchronousEmailBodyShouldContainALinkTo(string $link)
    {
        $this->populateDomCrawler();

        /** @var \ArrayIterator<int, \DOMElement> $linkElements */
        $linkElements = $this->emailDomCrawler->filter('a')->getIterator();

        foreach ($linkElements as $element) {
            if (preg_match('#^'.preg_quote($link, '/').'(.*)$#i', $element->attributes->getNamedItem('href')->textContent) > 0) {
                return true;
            }
        }
        throw new ExpectationException(\sprintf("Can't find a <a> element with a link to '%s'", $link), $this->getDriver());
    }

    /**
     * @Given this asynchronous email should contain an element matching selector :selector which value is :value
     */
    public function thisEmailShouldContainAnElementMatchingSelectorWhichValueIs($selector, $value)
    {
        $this->populateDomCrawler();

        /** @var \ArrayIterator<int, \DOMElement> $elements */
        $elements = $this->emailDomCrawler->filter($selector)->getIterator();

        foreach ($elements as $elem) {
            if ($elem->textContent === $value) {
                return true;
            }
        }

        throw new ExpectationException(\sprintf("can't find any element by selector %s matching requirements", $selector), $this->getDriver());
    }

    /**
     * @return TemplatedEmail[]
     */
    private function getQueuedMessages(): array
    {
        /** @var MessageDataCollector $collector */
        $collector = $this->getClientSymfonyProfile()->getCollector('mailer');

        return array_map(
            static fn (MessageEvent $event): TemplatedEmail => $event->getMessage(),
            array_filter($collector->getEvents()->getEvents(), static fn (MessageEvent $event): bool => $event->isQueued() && $event->getMessage() instanceof TemplatedEmail)
        );
    }

    private function setEmailPointer(?Email $email = null): void
    {
        $this->emailPointer = $email;
        $this->emailDomCrawler = null;
    }

    private function checkEmailPointer(): void
    {
        if (!$this->emailPointer instanceof TemplatedEmail) {
            throw new ExpectationException('No email have been selected yet. Consider using "I should get an email..." first.', $this->getDriver());
        }
    }

    private function populateDomCrawler(): void
    {
        $this->checkEmailPointer();

        if (null === $this->emailDomCrawler) {
            $this->emailDomCrawler = new Crawler($this->emailPointer->getHtmlBody());
        }
    }

    private function extractAdresses(array $source): array
    {
        return array_map(static fn (Address $address): string => $address->getAddress(), $source);
    }
}
