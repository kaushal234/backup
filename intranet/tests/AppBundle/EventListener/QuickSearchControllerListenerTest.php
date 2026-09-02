<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\EventListener;

use ApiBundle\Client;
use AppBundle\EventListener\QuickSearchControllerListener;
use AppBundle\Form\Type\IdSearchType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuickSearchControllerListenerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|FormFactoryInterface $formFactory;
    private ObjectProphecy|Client $client;
    private ObjectProphecy|RouterInterface $router;
    private ObjectProphecy|TranslatorInterface $translator;
    private QuickSearchControllerListener $listener;

    protected function setUp(): void
    {
        $this->formFactory = $this->prophesize(FormFactoryInterface::class);
        $this->client = $this->prophesize(Client::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);

        $this->listener = new QuickSearchControllerListener(
            $this->formFactory->reveal(),
            $this->client->reveal(),
            $this->router->reveal(),
            $this->translator->reveal()
        );
    }

    public function testOnKernelRequestReturnsEarlyWhenNotPost(): void
    {
        $request = new Request();
        $event = new RequestEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->formFactory->create(IdSearchType::class)->shouldNotBeCalled();

        $this->listener->onKernelRequest($event);
        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getResponse());
    }

    public function testOnKernelRequestReturnsEarlyWhenFormDataIsMissing(): void
    {
        $request = new Request([], ['other_form' => ['field' => 'value']]);
        $request->setMethod('POST');

        $event = new RequestEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->formFactory->create(IdSearchType::class)->shouldNotBeCalled();

        $this->listener->onKernelRequest($event);

        $this->assertFalse($event->isPropagationStopped());
        $this->assertNull($event->getResponse());
    }

    public function testOnKernelRequestRedirectsOnSuccess(): void
    {
        $id = '123';
        $apiRoute = '/api/items';
        $redirectRoute = 'app_item_show';

        $request = new Request([], [IdSearchType::NAME => ['id' => $id]]);
        $request->setMethod('POST');
        $event = new RequestEvent($this->prophesize(HttpKernelInterface::class)->reveal(), $request, HttpKernelInterface::MAIN_REQUEST);

        $form = $this->prophesize(FormInterface::class);
        $idField = $this->prophesize(FormInterface::class);
        $apiField = $this->prophesize(FormInterface::class);
        $redirectField = $this->prophesize(FormInterface::class);

        $form->handleRequest($request)->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);

        $idField->getData()->willReturn($id);
        $apiField->getData()->willReturn($apiRoute);
        $redirectField->getData()->willReturn($redirectRoute);

        $form->get('id')->willReturn($idField->reveal());
        $form->get('api_route')->willReturn($apiField->reveal());
        $form->get('redirect_route')->willReturn($redirectField->reveal());

        $this->formFactory->create(IdSearchType::class)->willReturn($form->reveal());

        $this->client->get('/api/items/123')->shouldBeCalled();
        $this->router->generate($redirectRoute, ['id' => $id])->willReturn('/final-url');

        $this->listener->onKernelRequest($event);

        $this->assertTrue($event->isPropagationStopped());
        $response = $event->getResponse();
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/final-url', $response->getTargetUrl());
    }

    public function testOnKernelRequestHandlesApiErrorWithFlashMessage(): void
    {
        $id = '404';
        $request = new Request([], [IdSearchType::NAME => ['id' => $id]]);
        $request->setMethod('POST');

        $flashBag = $this->prophesize(FlashBagInterface::class);
        $session = $this->prophesize(Session::class);
        $session->getFlashBag()->willReturn($flashBag->reveal());
        $request->setSession($session->reveal());

        $event = new RequestEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $form = $this->prophesize(FormInterface::class);
        $form->handleRequest($request)->willReturn($form->reveal());
        $form->isSubmitted()->willReturn(true);
        $form->isValid()->willReturn(true);

        $fieldStub = $this->prophesize(FormInterface::class);
        $fieldStub->getData()->willReturn($id);

        $form->get('id')->willReturn($fieldStub->reveal());
        $form->get('api_route')->willReturn($fieldStub->reveal());
        $form->get('redirect_route')->willReturn($fieldStub->reveal());

        $this->formFactory->create(IdSearchType::class)->willReturn($form->reveal());

        $dummyException = new class extends \RuntimeException implements ClientExceptionInterface {
            public function getResponse(): ResponseInterface
            {
                return (new \Prophecy\Prophet())->prophesize(ResponseInterface::class)->reveal();
            }
        };
        $this->client->get(Argument::type('string'))->willThrow($dummyException);
        $this->translator->trans('errors.not_exist', ['%id%' => $id])->willReturn('Traduction');
        $flashBag->add('error', 'Traduction')->shouldBeCalled();

        $this->listener->onKernelRequest($event);

        $this->assertFalse($event->isPropagationStopped());
    }
}
