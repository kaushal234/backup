<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components\Mis\TroubleTicket;

use AppBundle\Twig\Components\Mis\TroubleTicket\TroubleTicketCloseDynamicForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Tests\AppBundle\Twig\Components\LiveComponentTestCase;

class TroubleTicketCloseDynamicFormTest extends LiveComponentTestCase
{
    private const RESOURCE = 'mis/trouble_tickets/1';

    public function testMountStoresTicketAndKeepsOnlyClosingStatusesInAvailableOrder(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession();
        $this->mockApi(self::RESOURCE, $this->ticketFixture(1, [
            // Mixes non-closing statuses in to prove they are filtered out; order must follow availableStatus.
            'availableStatus' => ['IN PROGRESS', 'SOLVED', 'NOT AN ISSUE', 'REOPENED', 'NOT APPROVED'],
        ]), 200, 'GET');

        $component = $this->createLiveComponent(
            TroubleTicketCloseDynamicForm::class,
            ['troubleTicketId' => 1],
        )->component();

        self::assertSame(1, $component->troubleTicket['id']);
        self::assertSame(['SOLVED', 'NOT AN ISSUE', 'NOT APPROVED'], $component->statuses);
    }

    public function testMountDefaultsTasksToFalseWithoutSessionFlag(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession();
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'GET');

        $component = $this->createLiveComponent(
            TroubleTicketCloseDynamicForm::class,
            ['troubleTicketId' => 1],
        )->component();

        self::assertFalse($component->tasks);
    }

    public function testMountReadsTasksTrueFromSession(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession(['tasks' => ['some-task']]);
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'GET');

        $component = $this->createLiveComponent(
            TroubleTicketCloseDynamicForm::class,
            ['troubleTicketId' => 1],
        )->component();

        self::assertTrue($component->tasks);
    }

    public function testSaveRedirectsToShowPage(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession();
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'GET');
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'PUT');

        $wrapper = $this->createLiveComponent(TroubleTicketCloseDynamicForm::class, ['troubleTicketId' => 1]);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save');

        self::assertTrue($this->browser->getResponse()->isRedirect());
        self::assertStringContainsString(
            '/mis/trouble-tickets/1/show',
            $this->browser->getResponse()->headers->get('Location'),
        );
    }

    public function testSaveSendsStatusAndCommentToApi(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession();
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'GET');
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'PUT');

        $wrapper = $this->createLiveComponent(TroubleTicketCloseDynamicForm::class, ['troubleTicketId' => 1]);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save');

        $body = $this->getLastCapturedApiRequestBody();
        self::assertNotNull($body);
        self::assertSame('NOT AN ISSUE', $body['status']);
        self::assertSame('Closing reason', $body['comment']);
    }

    public function testSaveMapsApiViolationsWhenClientExceptionThrown(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession();
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'GET');
        $this->mockApi(self::RESOURCE, [
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'violations' => [['propertyPath' => 'comment', 'message' => 'Invalid.']],
        ], 422, 'PUT');

        $wrapper = $this->createLiveComponent(TroubleTicketCloseDynamicForm::class, ['troubleTicketId' => 1]);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save');

        self::assertSame(422, $this->browser->getResponse()->getStatusCode());
    }

    /**
     * Parity with the old React validationClose.ts: choosing SOLVED without a satisfaction rating
     * must fail local validation (422) and never reach the API. No PUT is mocked, so an API call
     * would surface as an unexpected-request failure rather than a passing test.
     */
    public function testSolvedStatusWithoutSatisfactionIsRejectedBeforeApiCall(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession();
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'GET');

        $wrapper = $this->createLiveComponent(TroubleTicketCloseDynamicForm::class, ['troubleTicketId' => 1]);
        $this->setValidFormValues($wrapper, 'SOLVED');
        $wrapper->call('save');

        self::assertSame(422, $this->browser->getResponse()->getStatusCode());
    }

    public function testSolvedStatusWithSatisfactionSucceeds(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession();
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'GET');
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'PUT');

        $wrapper = $this->createLiveComponent(TroubleTicketCloseDynamicForm::class, ['troubleTicketId' => 1]);
        $this->setValidFormValues($wrapper, 'SOLVED');
        $wrapper->set('trouble_ticket_close.satisfaction', 'Satisfied');
        $wrapper->call('save');

        self::assertTrue($this->browser->getResponse()->isRedirect());
    }

    /**
     * The `next` LiveArg makes save() redirect to the `next_trouble_ticket` route
     * (NextController, path /{id}/next-trouble-ticket) rather than the show page.
     */
    public function testSaveWithNextRedirectsToNextTicketRoute(): void
    {
        $this->login('superuser');
        $this->pushRequestWithSession();
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'GET');
        $this->mockApi(self::RESOURCE, $this->ticketFixture(), 200, 'PUT');

        $wrapper = $this->createLiveComponent(TroubleTicketCloseDynamicForm::class, ['troubleTicketId' => 1]);
        $this->setValidFormValues($wrapper);
        $wrapper->call('save', ['next' => '1']);

        self::assertTrue($this->browser->getResponse()->isRedirect());
        self::assertStringContainsString(
            '/mis/trouble-tickets/1/next-trouble-ticket',
            $this->browser->getResponse()->headers->get('Location'),
        );
    }

    /**
     * mount() reads the "tasks" session flag via the RequestStack. When the component is mounted
     * directly through the ComponentFactory (as the test harness does), no request/session is on
     * the stack, so getSession() throws. Push a request carrying a session to reproduce the real
     * initial-render context, and optionally seed session values.
     *
     * @param array<string, mixed> $sessionData
     */
    private function pushRequestWithSession(array $sessionData = []): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        foreach ($sessionData as $key => $value) {
            $session->set($key, $value);
        }

        $request = new Request();
        $request->setSession($session);

        static::getContainer()->get('request_stack')->push($request);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function ticketFixture(int $id = 1, array $overrides = []): array
    {
        return array_merge([
            '@id' => \sprintf('/mis/trouble_tickets/%d', $id),
            'id' => $id,
            'availableStatus' => ['SOLVED', 'NOT AN ISSUE', 'NOT APPROVED'],
            'ccs' => [],
        ], $overrides);
    }

    private function setValidFormValues(object $wrapper, string $status = 'NOT AN ISSUE'): void
    {
        // `comment` and `status` are the only NotBlank fields; satisfaction is required only for SOLVED.
        $wrapper->set('trouble_ticket_close.comment', 'Closing reason');
        $wrapper->set('trouble_ticket_close.status', $status);
    }
}
